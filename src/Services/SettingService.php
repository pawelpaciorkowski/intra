<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Setting;
use App\Entity\UserSetting;
use App\Services\Component\ParameterBag;
use App\Services\Component\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

use function count;
use function property_exists;
use function serialize;
use function settype;
use function unserialize;

final class SettingService
{
    public const string SESSION_NAME = 'alab.setting.manager';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function saveSettings(array $data, UserInterface $user): bool
    {
        foreach ($data as $key => $value) {
            $setting = $this->entityManager->getRepository(Setting::class)->findOneBy(['key' => $key]);

            $userSetting = $this->entityManager->getRepository(UserSetting::class)->findOneBy(
                [
                    'user' => $user,
                    'setting' => $setting,
                ]
            );

            settype($value, $setting->getField()->getLangType());

            if ($userSetting) {
                $userSetting->setValue(serialize($value));
            } else {
                $userSetting = new UserSetting();
                $userSetting->setValue(serialize($value));
                $userSetting->setUser($user);
                $userSetting->setSetting($setting);
                $this->entityManager->persist($userSetting);
            }
        }
        $this->entityManager->flush();

        return $this->reloadSettings($user);
    }

    public function reloadSettings(UserInterface $user): bool
    {
        $this->requestStack->getSession()->set($this::SESSION_NAME, new stdClass());

        $this
            ->reloadGlobalSettings()
            ->reloadUserSettings($user);

        return true;
    }

    private function reloadUserSettings(UserInterface $user): void
    {
        $repository = $this->entityManager->getRepository(UserSetting::class);
        $userSettings = $repository->findAllByParams(
            new ParameterBag([
                'is-active' => 1,
                'user' => $user,
            ])
        );

        foreach ($userSettings as $userSetting) {
            $this->requestStack->getSession()->get($this::SESSION_NAME)->{$userSetting->getSetting()->getKey()} =
                unserialize(
                    $userSetting->getValue(),
                    ['allowed_classes' => true]
                );
        }
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null, ?UserInterface $user = null): mixed
    {
        // Use restriction
        if ($parameterBag && $parameterBag->has('restrict') && $parameterBag->get('restrict')) {
            $parameterBag->add(['user' => $user]);
        }

        return $this->entityManager->getRepository(Setting::class)->findAllByParams($parameterBag);
    }

    public function getSetting(string $settingName, UserInterface $user)
    {
        if (!$this->requestStack->getSession()->has($this::SESSION_NAME)) {
            $this->reloadSettings($user);
        }

        if (property_exists($this->requestStack->getSession()->get($this::SESSION_NAME), $settingName)) {
            return $this->requestStack->getSession()->get($this::SESSION_NAME)->$settingName;
        }

        return null;
    }

    private function reloadGlobalSettings(): self
    {
        $oRepository = $this->entityManager->getRepository(Setting::class);
        $settings = $oRepository->findAll();

        foreach ($settings as $setting) {
            $this->requestStack->getSession()->get($this::SESSION_NAME)->{$setting->getKey()} = unserialize(
                $setting->getDefault(),
                ['allowed_classes' => true]
            );
        }

        return $this;
    }

    public function getSettingForUser(string $settingName, UserInterface $user)
    {
        $userSettings = $this->entityManager->getRepository(UserSetting::class)->findAllByParams(
            new ParameterBag([
                'is-active' => 1,
                'user' => $user,
                'key' => $settingName,
            ])
        );

        if (1 !== (is_countable($userSettings) ? count($userSettings) : 0)) {
            return null;
        }

        return unserialize($userSettings[0]->getValue(), ['allowed_classes' => true]);
    }

    public function getSettingsAsArray(): array
    {
        return (array)$this->requestStack->getSession()->get($this::SESSION_NAME);
    }
}
