<?php

namespace App\Entity;

use App\Doctrine\Common\Collections\ArrayCollection;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question
{
    const TAG_PROFILE_FILL_FIRST_SCREEN = 'TAG_PROFILE_FILL_FIRST_SCREEN';
    const TAG_PROFILE_FILL_INFO_SCREEN = 'TAG_PROFILE_FILL_INFO_SCREEN';
    const TAG_PROFILE_NEW_CALL = 'TAG_PROFILE_NEW_CALL';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $multiple = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="question")
     * @OrderBy({"id" = "ASC"})
     */
    private $answers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tag;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $declarativeTitle;

    /**
     * @ORM\ManyToMany(targetEntity=Community::class, inversedBy="questions")
     */
    private $communities;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->communities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMultiple(): ?bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function getStringAnswers()
    {
        return implode(', ', $this->getAnswers()->toArray());
    }

    /**
     * @param Collection $collection
     * @return Question
     */
    public function setAnswers(Collection $collection): self
    {
        $this->answers = $collection;
        return $this;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getDeclarativeTitle(): ?string
    {
        return $this->declarativeTitle;
    }

    public function setDeclarativeTitle(?string $declarativeTitle): self
    {
        $this->declarativeTitle = $declarativeTitle;

        return $this;
    }

    /**
     * @return Collection|Community[]
     */
    public function getCommunities(): Collection
    {
        return $this->communities;
    }

    public function addCommunity(Community $community): self
    {
        if (!$this->communities->contains($community)) {
            $this->communities[] = $community;
        }

        return $this;
    }

    public function removeCommunity(Community $community): self
    {
        if ($this->communities->contains($community)) {
            $this->communities->removeElement($community);
        }

        return $this;
    }
}
