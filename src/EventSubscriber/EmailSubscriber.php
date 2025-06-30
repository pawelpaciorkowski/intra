<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Team;
use App\Entity\TemplateTag;
use App\Entity\UserSetting;
use App\Event\EmailEvent;
use App\Services\Attachment\Attachments;
use App\Services\Attachment\AttachmentService;
use App\Services\Component\ParameterBag;
use App\Services\PsajdakService;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

final readonly class EmailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Environment $twigEnvironment,
        private ValidatorInterface $validator,
        private ParameterBagInterface $parameterBag,
        private LoggerInterface $mailLogger,
        private AttachmentService $attachmentService,
        private PsajdakService $psajdakService
    ) {
    }

    #[ArrayShape([EmailEvent::class => "string[]"])]
    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvent::class => ['emailSend'],
        ];
    }

    /**
     * @throws TransportExceptionInterface|JsonException
     */
    public function emailSend(EmailEvent $event): void
    {
        $tags = $this
            ->entityManager
            ->getRepository(TemplateTag::class)
            ->findAllByParams(
                new ParameterBag([
                    'tag' => $event->getTrigger(),
                    'is-active' => true,
                ])
            );

        if ($tags) {
            foreach ($tags as $tag) {
                foreach ($tag->getTemplates() as $template) {
                    $strings = $this->renderTwigStrings(
                        [
                            'body' => $template->getBody(),
                            'subject' => $template->getSubject(),
                            'recipient' => $template->getRecipient(),
                        ],
                        $event->getDataForTemplate()
                    );

                    if ($strings['recipient'] = $this->parseRecipients($strings['recipient'])) {
                        $attachments = null;
                        if ($template->getAttachment()) {
                            $attachments = $this
                                ->attachmentService
                                ->setContextData($event->getDataForTemplate())
                                ->parse($template->getAttachment())
                                ->getAttachments();
                        }

                        $sender = [
                            'name' => $template->getSenderName() ?? $this->parameterBag->get('email')['title_prefix'],
                            'email' => $template->getSenderAddress() ?? $this->parameterBag->get('email')['mailer_sender'],
                        ];

                        $this->send(
                            $sender,
                            $strings['recipient'],
                            $strings['subject'],
                            $strings['body'],
                            $attachments
                        );
                    }
                }
            }
        }
    }

    /**
     * Render twig strings with dataForTemplate data.
     */
    private function renderTwigStrings(array $strings, array $dataForTemplate): array
    {
        $twig = clone $this->twigEnvironment;
        $result = [];

        foreach ($strings as $key => $string) {
            $result[$key] = $twig->createTemplate($string)->render($dataForTemplate);
        }

        return $result;
    }

    /**
     * Parse recipients string - create array of e-mail addresses.
     */
    private function parseRecipients(string $recipient): array
    {
        $analyzedRecipient = [];
        $constraints = [new EmailConstraint(), new NotBlank()];

        $addresses = preg_split('/[,\s]+/', $recipient, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($addresses as $adres) {
            $adres = trim($adres);

            if (preg_match('/^ROLE_[A-Z]+$/', $adres)) {
                if ($teams = $this->entityManager->getRepository(Team::class)->findByRoles($adres)) {
                    foreach ($teams as $team) {
                        foreach ($team->getUsers() as $user) {
                            if ($user->getIsActive()) {
                                $analyzedRecipient[] = $user->getEmail();
                            }
                        }
                    }
                }
            } elseif (preg_match('/^SETTING:(.+)=(.+)$/', $adres, $matches)) {
                if ($userSettings = $this->entityManager->getRepository(UserSetting::class)->findAllByParams(
                    new ParameterBag([
                        'key' => $matches[1],
                        'value' => $matches[2],
                        'is-active' => true,
                    ])
                )
                ) {
                    foreach ($userSettings as $userSetting) {
                        if ($userSetting->getUser()->getIsActive()) {
                            $analyzedRecipient[] = $userSetting->getUser()->getEmail();
                        }
                    }
                }
            } elseif (0 === count($this->validator->validate($adres, $constraints))) {
                $analyzedRecipient[] = $adres;
            }
        }

        return array_unique($analyzedRecipient);
    }

    private function send(array $from, array $to, string $subject, string $body, ?Attachments $attachment = null): void
    {
        $message = [
            'sender_info' => 'app.name:intranet',
            'type' => 'mail',
            'from_name' => $from['name'],
            'from_address' => $from['email'],
            'to_addresses' => $to,
            'subject' => $subject,
            'text_content' => strip_tags(preg_replace('/<br(\s*)?\/?>/i', PHP_EOL, $body)),
            'html_content' => $body,
        ];

        if ($attachment) {
            $message['base64_attachments'] = [];
            foreach ($attachment->getAttachments() as $file) {
                $message['base64_attachments'][] = [
                    'file_name' => $file->getFilename(),
                    'data' => base64_encode($file->getContent())
                ];
            }
        }

        $this->psajdakService->publish($message);

        $this->mailLogger->info('Send to queue', [
            'to' => $to,
            'from' => $from,
            'subject' => $subject,
        ]);
    }
}
