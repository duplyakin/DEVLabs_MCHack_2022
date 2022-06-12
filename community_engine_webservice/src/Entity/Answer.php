<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnswerRepository::class)
 */
class Answer
{
    const TYPE_PUBLIC = 1;
    const TYPE_PRIVATE = 2;
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
     * @ORM\ManyToOne(targetEntity=Answer::class, inversedBy="relatedAnswers", cascade={"persist"})
     */
    private $relatedAnswer;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="relatedAnswer", cascade={"persist"})
     */
    private $relatedAnswers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="answers", cascade={"persist"})
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=Question::class, inversedBy="answers", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @ORM\ManyToMany(targetEntity=CallStep::class, mappedBy="answers")
     */
    private $callSteps;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @ORM\Column(type="integer")
     */
    private $type = self::TYPE_PUBLIC;

    public function __construct()
    {
        $this->relatedAnswers = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->callSteps = new ArrayCollection();
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

    public function getRelatedAnswer(): ?self
    {
        return $this->relatedAnswer;
    }

    public function setRelatedAnswer(?self $relatedAnswer): self
    {
        $this->relatedAnswer = $relatedAnswer;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getRelatedAnswers(): Collection
    {
        return $this->relatedAnswers;
    }

    public function addRelatedAnswer(self $relatedAnswer): self
    {
        if (!$this->relatedAnswers->contains($relatedAnswer)) {
            $this->relatedAnswers[] = $relatedAnswer;
            $relatedAnswer->setRelatedAnswer($this);
        }

        return $this;
    }

    public function removeRelatedAnswer(self $relatedAnswer): self
    {
        if ($this->relatedAnswers->contains($relatedAnswer)) {
            $this->relatedAnswers->removeElement($relatedAnswer);
            // set the owning side to null (unless already changed)
            if ($relatedAnswer->getRelatedAnswer() === $this) {
                $relatedAnswer->setRelatedAnswer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addAnswer($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeAnswer($this);
        }

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Collection|CallStep[]
     */
    public function getCallSteps(): Collection
    {
        return $this->callSteps;
    }

    public function addCallStep(CallStep $callStep): self
    {
        if (!$this->callSteps->contains($callStep)) {
            $this->callSteps[] = $callStep;
            $callStep->addAnswer($this);
        }

        return $this;
    }

    public function removeCallStep(CallStep $callStep): self
    {
        if ($this->callSteps->contains($callStep)) {
            $this->callSteps->removeElement($callStep);
            $callStep->removeAnswer($this);
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
