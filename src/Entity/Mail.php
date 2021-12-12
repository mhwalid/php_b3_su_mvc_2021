<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="mail")
 */
class Mail
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $fromMail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $fromName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $toMail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $subject;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $message;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $fileName;

    /**
     * @ORM\Column(type="string", length=255 , nullable=true)
     */
    private string $replyToMail;

    /**
     * @ORM\Column(type="array" , nullable=true)
     */
    private array $cc = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private DateTime $dateSend;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFromMail(): string
    {
        return $this->fromMail;
    }

    /**
     * @param string $fromMail
     */
    public function setFromMail(string $fromMail): void
    {
        $this->fromMail = $fromMail;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     */
    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }

    /**
     * @return string
     */
    public function getToMail(): string
    {
        return $this->toMail;
    }

    /**
     * @param string $toMail
     */
    public function setToMail(string $toMail): void
    {
        $this->toMail = $toMail;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getReplyToMail(): string
    {
        return $this->replyToMail;
    }

    /**
     * @param string $replyToMail
     */
    public function setReplyToMail(string $replyToMail): void
    {
        $this->replyToMail = $replyToMail;
    }

    /**
     * @return array
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param array $cc
     */
    public function setCc(array $cc): void
    {
        $this->cc = $cc;
    }

    /**
     * @return DateTime
     */
    public function getDateSend(): DateTime
    {
        return $this->dateSend;
    }

    /**
     * @param DateTime $dateSend
     */
    public function setDateSend(DateTime $dateSend): void
    {
        $this->dateSend = $dateSend;
    }

}