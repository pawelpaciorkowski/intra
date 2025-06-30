<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LogDbTrackingRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

#[ORM\Table(name: "log_db_trackings")]
#[ORM\Entity(repositoryClass: LogDbTrackingRepository::class)]
class LogDbTracking extends AbstractLogEntry
{
}
