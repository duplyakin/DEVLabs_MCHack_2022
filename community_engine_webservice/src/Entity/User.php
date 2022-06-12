<?php

namespace App\Entity;

use App\Doctrine\Common\Collections\ArrayCollection;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(
 *     fields={"emailAlt", "email"},
 *     errorPath="emailAlt",
 *     repositoryMethod="checkEmail",
 *     message="This email is already in use"
 * )
 */
class User implements UserInterface
{
    const INVITE_COOKIE_KEY = '__Invite_User';
    const INVITE_COMMUNITY_COOKIE_KEY = '__Invite_Community';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Email
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @AppAssert\Answer
     * @ORM\ManyToMany(targetEntity=Answer::class, inversedBy="users", fetch="EAGER", cascade={"persist", "refresh"})
     */
    private $answers;

    /**
     * @ORM\ManyToMany(targetEntity=Community::class, mappedBy="users", indexBy="url", cascade={"persist", "refresh"})
     */
    private $communities;

    /**
     * @ORM\OneToMany(targetEntity=CallUser::class, mappedBy="user")
     */
    private $callItem;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telegramUsername;

    /**
     * @Assert\NotBlank(groups={"create_profile"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $googleId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $questionComplete = 0;

    /**
     * @Assert\Email(groups={"create_profile"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailAlt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hold = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ready = 0;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $readyToMatch;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $doNotDisturb;

    /**
     * @Assert\NotBlank(groups={"create_profile"})
     * @Assert\Length(max="255", groups={"create_profile"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $about;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $linkedinLink;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="myInvitedUsers", cascade={"persist", "refresh"})
     */
    private $invitedBy;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="invitedBy", cascade={"persist", "refresh"})
     */
    private $myInvitedUsers;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $publicId;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="user")
     */
    private $reviews;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $google_access_token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $google_refresh_token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $facebook_access_token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $facebook_refresh_token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $looking_for;

    /**
     * @var array
     */
    private $userConnects = [];

    private $intersectAnswers = [];

    /**
     * @ORM\OneToMany(targetEntity=UserMetric::class, mappedBy="user")
     */
    private $metrics;

    private $directAnswerIds = [];

    /**
     * @ORM\OneToMany(targetEntity=ConnectNote::class, mappedBy="user")
     */
    private $connectNotes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $telegramId;

    /**
     * @ORM\OneToMany(targetEntity=BalanceTransaction::class, mappedBy="user")
     */
    private $balanceTransactions;

    /**
     * @ORM\ManyToMany(targetEntity=Certificate::class, mappedBy="used_users")
     */
    private $certificates;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $balance;

    /**
     * @ORM\OneToMany(targetEntity=UserCommunitySetting::class, mappedBy="user")
     */
    private $userCommunitySettings;

    /**
     * @ORM\OneToMany(targetEntity=UserCommunityBalance::class, mappedBy="user")
     */
    private $userCommunityBalances;

    /**
     * @ORM\Column(type="boolean")
     */
    private $profile_complete = 0;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="rate_to")
     */
    private $me_reviews;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $temp_token;

    /**
     * @ORM\OneToMany(targetEntity=MetricOrder::class, mappedBy="user")
     */
    private $metricOrders;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=UserAction::class, mappedBy="user")
     */
    private $userActions;

    /**
     * @ORM\ManyToOne(targetEntity=Community::class, inversedBy="invitedUsers")
     */
    private $invitedToCommunity;

    /**
     * @ORM\ManyToMany(targetEntity=Community::class, inversedBy="managers")
     * @JoinTable(name="manager_community",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="community_id", referencedColumnName="id")}
     * )
     */
    private $manageCommunities;

    /**
     * @Assert\Regex(
     *     groups={"create_profile"},
     *     pattern="/https:\/\/(|www\.)calendly.com\/([a-zA-Z0-9-_\/]*)/i",
     *     message="Wrong Calendly link"
     * )
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $calendlyLink;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->communities = new ArrayCollection();
        $this->callItem = new ArrayCollection();
        $this->myInvitedUsers = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->publicId = uniqid();
        $this->temp_token = uniqid('', true);
        $this->setCreatedAt(new \DateTime());
//        $this->options = new \Doctrine\Common\Collections\ArrayCollection();
        $this->metrics = new \Doctrine\Common\Collections\ArrayCollection();
        $this->connectNotes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->balanceTransactions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->certificates = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userCommunitySettings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userCommunityBalances = new \Doctrine\Common\Collections\ArrayCollection();
        $this->me_reviews = new \Doctrine\Common\Collections\ArrayCollection();
        $this->metricOrders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userActions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->manageCommunities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param string $role
     * @return User
     */
    public function addRole(string $role): self
    {
        $this->roles[] = $role;
        $this->roles = $this->getRoles();

        return $this;
    }

    /**
     * @param string $role
     * @return User
     */
    public function removeRole(string $role): self
    {
        foreach ($this->roles as $key => $item) {
            if ($role == $item) {
                unset($this->roles[$key]);
                break;
            }
        }
        $this->roles = $this->getRoles();

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function setAnswers(array $answers)
    {
        $this->answers = new ArrayCollection($answers);
        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->addUser($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
        }

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
            $community->addUser($this);
        }

        return $this;
    }

    public function removeCommunity(Community $community): self
    {
        if ($this->communities->contains($community)) {
            $this->communities->removeElement($community);
            $community->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|CallUser[]
     */
    public function getCallItem(): Collection
    {
        return $this->callItem;
    }

    public function addCallItem(CallUser $callItem): self
    {
        if (!$this->callItem->contains($callItem)) {
            $this->callItem[] = $callItem;
            $callItem->setUser($this);
        }

        return $this;
    }

    public function removeCallItem(CallUser $callItem): self
    {
        if ($this->callItem->contains($callItem)) {
            $this->callItem->removeElement($callItem);
            // set the owning side to null (unless already changed)
            if ($callItem->getUser() === $this) {
                $callItem->setUser(null);
            }
        }

        return $this;
    }

    public function getFacebookId(): ?int
    {
        return $this->facebookId;
    }

    public function setFacebookId(?int $facebookId): self
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    public function getTelegramUsername(): ?string
    {
        return $this->telegramUsername;
    }

    public function setTelegramUsername(?string $telegramUsername): self
    {
        if (!$telegramUsername) {
            $this->telegramUsername = null;
            return $this;
        }
        if ($path = parse_url($telegramUsername, PHP_URL_PATH)) {
            $telegramUsername = trim($path, '/');
        }
        $telegramUsername = preg_replace('/[^A-Za-z0-9\_\@]/', '', $telegramUsername);
        if (substr($telegramUsername, 0, 1) != '@') {
            $telegramUsername = '@' . $telegramUsername;
        }
        $this->telegramUsername = $telegramUsername;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getPictureUrl()
    {
        return '/upload/' . $this->getPicture();
    }

    public function getFacebookLink(): ?string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): self
    {
        $this->facebookLink = $facebookLink;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getQuestionComplete(): ?bool
    {
        return $this->questionComplete;
    }

    public function setQuestionComplete(?bool $questionComplete): self
    {
        $this->questionComplete = $questionComplete;

        return $this;
    }

    public function getEmailAlt(): ?string
    {
        return $this->emailAlt;
    }

    public function setEmailAlt(?string $emailAlt): self
    {
        $this->emailAlt = $emailAlt;

        return $this;
    }

    public function getHold(): ?bool
    {
        return $this->hold;
    }

    public function setHold(bool $hold): self
    {
        $this->hold = $hold;

        return $this;
    }

    public function getActualEmail()
    {
        return $this->getEmailAlt() ?? $this->getEmail();
    }

    public function getReady(): ?bool
    {
        return $this->ready;
    }

    public function setReady(bool $ready): self
    {
        $this->ready = $ready;

        return $this;
    }

    public function getReadyToMatch(): ?bool
    {
        return $this->readyToMatch;
    }

    public function setReadyToMatch(?bool $readyToMatch): self
    {
        $this->readyToMatch = $readyToMatch;

        return $this;
    }

    public function getDoNotDisturb()
    {
        return (bool)$this->doNotDisturb;
    }

    public function setDoNotDisturb($doNotDisturb): self
    {
        if (!($doNotDisturb instanceof \DateTimeInterface)) {
            $doNotDisturb = $doNotDisturb ? new \DateTime() : null;
        }
        $this->doNotDisturb = $doNotDisturb;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = preg_replace("/[^А-Я-а-яA-Za-z0-9\.\,\-?!\s]/u", "", $about);

        return $this;
    }

    public function setRawAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getLinkedinLink(): ?string
    {
        return $this->linkedinLink;
    }

    public function setLinkedinLink(?string $linkedinLink): self
    {
        $this->linkedinLink = $linkedinLink;

        return $this;
    }

    public function getInvitedBy(): ?self
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?self $invitedBy): self
    {
        $this->invitedBy = $invitedBy;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getMyInvitedUsers(): Collection
    {
        return $this->myInvitedUsers;
    }

    public function addMyInvitedUser(self $myInvitedUser): self
    {
        if (!$this->myInvitedUsers->contains($myInvitedUser)) {
            $this->myInvitedUsers[] = $myInvitedUser;
            $myInvitedUser->setInvitedBy($this);
        }

        return $this;
    }

    public function removeMyInvitedUser(self $myInvitedUser): self
    {
        if ($this->myInvitedUsers->contains($myInvitedUser)) {
            $this->myInvitedUsers->removeElement($myInvitedUser);
            // set the owning side to null (unless already changed)
            if ($myInvitedUser->getInvitedBy() === $this) {
                $myInvitedUser->setInvitedBy(null);
            }
        }

        return $this;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function setPublicId(?string $publicId): self
    {
        $this->publicId = $publicId;

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getGoogleAccessToken(): ?string
    {
        return $this->google_access_token;
    }

    public function setGoogleAccessToken(?string $google_access_token): self
    {
        $this->google_access_token = $google_access_token;

        return $this;
    }

    public function getGoogleRefreshToken(): ?string
    {
        return $this->google_refresh_token;
    }

    public function setGoogleRefreshToken(?string $google_refresh_token): self
    {
        $this->google_refresh_token = $google_refresh_token;

        return $this;
    }

    public function getFacebookAccessToken(): ?string
    {
        return $this->facebook_access_token;
    }

    public function setFacebookAccessToken(?string $facebook_access_token): self
    {
        $this->facebook_access_token = $facebook_access_token;

        return $this;
    }

    public function getFacebookRefreshToken(): ?string
    {
        return $this->facebook_refresh_token;
    }

    public function setFacebookRefreshToken(?string $facebook_refresh_token): self
    {
        $this->facebook_refresh_token = $facebook_refresh_token;

        return $this;
    }

    public function getLookingFor(): ?string
    {
        return $this->looking_for;
    }

    public function setLookingFor(?string $lookingFor): self
    {
        $lookingFor = preg_replace("/[^А-Я-а-яA-Za-z0-9\-?!\s]/u", "", $lookingFor);
        $this->looking_for = $lookingFor;

        return $this;
    }

    public function setRawLookingFor(?string $lookingFor): self
    {
        $this->looking_for = $lookingFor;

        return $this;
    }

    //////////////////////
    //TODO make true sql
    /**
     * @param array $tags
     * @return Collection
     */
    public function getQuestions(array $tags = [Question::TAG_PROFILE_FILL_INFO_SCREEN]): Collection
    {
        $questions = new ArrayCollection();
        $answers = $this->getAnswers()->filter(function (Answer $answer) use ($tags) {
            $answer->getQuestion()->setAnswers(new ArrayCollection());
            return in_array($answer->getQuestion()->getTag(), $tags);
        });
        $answers->map(function (Answer $answer) use ($questions) {
            $questions->add($answer->getQuestion()->addAnswer($answer));
        });
        return $questions;
    }

//    public function getMetric(): ?Metric
//    {
//        return $this->metric;
//    }
//
//    public function setMetric(Metric $metric): self
//    {
//        $this->metric = $metric;
//
//        // set the owning side of the relation if necessary
//        if ($metric->getUser() !== $this) {
//            $metric->setUser($this);
//        }
//
//        return $this;
//    }

    public function getFullName(string $delimiter = ' ')
    {
        return $this->getFirstName() . $delimiter . $this->getLastName();
    }

    /**
     * @return Collection|UserMetric[]
     */
    public function getMetrics(): Collection
    {
        return $this->metrics;
    }

    public function addMetric(UserMetric $metric): self
    {
        if (!$this->metrics->contains($metric)) {
            $this->metrics[] = $metric;
            $metric->setUser($this);
        }

        return $this;
    }

    public function removeMetric(UserMetric $metric): self
    {
        if ($this->metrics->contains($metric)) {
            $this->metrics->removeElement($metric);
            // set the owning side to null (unless already changed)
            if ($metric->getUser() === $this) {
                $metric->setUser(null);
            }
        }

        return $this;
    }

    //TODO
    public function getFirstMetricValueByField(UserMetricField $metricField)
    {
        $result = $this->getFirstMetricByField($metricField);
        return $result ? $result->getValue() : null;
    }

    public function getMetricOrderByCommunity(Community $community)
    {
        return $this->getMetricOrders()->filter(function (MetricOrder $metricOrder) use ($community) {
            return $community->getId() == $metricOrder->getCommunity()->getId();
        })->first();
    }

    /**
     * @param UserMetricField $metricField
     * @return UserMetric|null
     */
    public function getFirstMetricByField(UserMetricField $metricField)
    {
        return $this->getMetrics()->filter(function (UserMetric $metric) use ($metricField) {
            return $metric->getField() instanceof UserMetricField &&
                $metric->getField()->getId() == $metricField->getId();
        })->first();
    }

    /**
     * @return Collection
     */
    public function getDirectAnswerIds()
    {
        if (!$this->directAnswerIds) {
            $this->directAnswerIds = $this->getAnswers()->map(function (Answer $answer) {
                return $answer->getRelatedAnswer() ? $answer->getRelatedAnswer()->getId() : $answer->getId();
            })->toArray();
        }
        return $this->directAnswerIds;
    }

    /**
     * @return array
     */
    public function getUserConnects(): array
    {
        return $this->userConnects;
    }

    /**
     * @param array $userConnects
     * @return User
     */
    public function setUserConnects(array $userConnects): self
    {
        $this->userConnects = $userConnects;
        return $this;
    }

    /**
     * @return array
     */
    public function getIntersectAnswers(): array
    {
        return $this->intersectAnswers;
    }

    /**
     * @param array $intersectAnswers
     * @return User
     */
    public function setIntersectAnswers(array $intersectAnswers): self
    {
        $this->intersectAnswers = $intersectAnswers;
        return $this;
    }

    /**
     * @return Collection|ConnectNote[]
     */
    public function getConnectNotes(): Collection
    {
        return $this->connectNotes;
    }

    public function addConnectNote(ConnectNote $connectNote): self
    {
        if (!$this->connectNotes->contains($connectNote)) {
            $this->connectNotes[] = $connectNote;
            $connectNote->setUser($this);
        }

        return $this;
    }

    public function removeConnectNote(ConnectNote $connectNote): self
    {
        if ($this->connectNotes->contains($connectNote)) {
            $this->connectNotes->removeElement($connectNote);
            // set the owning side to null (unless already changed)
            if ($connectNote->getUser() === $this) {
                $connectNote->setUser(null);
            }
        }

        return $this;
    }

    public function getTelegramId(): ?int
    {
        return $this->telegramId;
    }

    public function setTelegramId(?int $telegramId): self
    {
        $this->telegramId = $telegramId;

        return $this;
    }

    public function isUseTelegram()
    {
        return (bool)$this->getTelegramId();
    }

    /**
     * @return Collection|BalanceTransaction[]
     */
    public function getBalanceTransactions(): Collection
    {
        return $this->balanceTransactions;
    }

    public function addBalanceTransaction(BalanceTransaction $balanceTransaction): self
    {
        if (!$this->balanceTransactions->contains($balanceTransaction)) {
            $this->balanceTransactions[] = $balanceTransaction;
            $balanceTransaction->setUser($this);
        }

        return $this;
    }

    public function removeBalanceTransaction(BalanceTransaction $balanceTransaction): self
    {
        if ($this->balanceTransactions->contains($balanceTransaction)) {
            $this->balanceTransactions->removeElement($balanceTransaction);
            // set the owning side to null (unless already changed)
            if ($balanceTransaction->getUser() === $this) {
                $balanceTransaction->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Certificate[]
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): self
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates[] = $certificate;
            $certificate->addUsedUser($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): self
    {
        if ($this->certificates->contains($certificate)) {
            $this->certificates->removeElement($certificate);
            $certificate->removeUsedUser($this);
        }

        return $this;
    }

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(?int $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getPaidCommunities()
    {
        return $this->getCommunities()->filter(function (Community $community) {
            return $community->getIsPaid();
        });
    }

    public function containAnswerById(int $id)
    {
        return $this->getAnswers()->exists(function ($key, Answer $answer) use ($id) {
            return $answer->getId() == $id;
        });
    }

    /**
     * @return Collection|UserCommunitySetting[]
     */
    public function getUserCommunitySettings(): Collection
    {
        return $this->userCommunitySettings;
    }

    public function addUserCommunitySetting(UserCommunitySetting $userCommunitySetting): self
    {
        if (!$this->userCommunitySettings->contains($userCommunitySetting)) {
            $this->userCommunitySettings[] = $userCommunitySetting;
            $userCommunitySetting->setUser($this);
        }

        return $this;
    }

    public function removeUserCommunitySetting(UserCommunitySetting $userCommunitySetting): self
    {
        if ($this->userCommunitySettings->contains($userCommunitySetting)) {
            $this->userCommunitySettings->removeElement($userCommunitySetting);
            // set the owning side to null (unless already changed)
            if ($userCommunitySetting->getUser() === $this) {
                $userCommunitySetting->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserCommunityBalance[]
     */
    public function getUserCommunityBalances(): Collection
    {
        return $this->userCommunityBalances;
    }

    public function addUserCommunityBalance(UserCommunityBalance $userCommunityBalance): self
    {
        if (!$this->userCommunityBalances->contains($userCommunityBalance)) {
            $this->userCommunityBalances[] = $userCommunityBalance;
            $userCommunityBalance->setUser($this);
        }

        return $this;
    }

    public function removeUserCommunityBalance(UserCommunityBalance $userCommunityBalance): self
    {
        if ($this->userCommunityBalances->contains($userCommunityBalance)) {
            $this->userCommunityBalances->removeElement($userCommunityBalance);
            // set the owning side to null (unless already changed)
            if ($userCommunityBalance->getUser() === $this) {
                $userCommunityBalance->setUser(null);
            }
        }

        return $this;
    }

    public function getProfileComplete(): ?bool
    {
        return $this->profile_complete;
    }

    public function setProfileComplete(bool $profile_complete): self
    {
        $this->profile_complete = $profile_complete;

        return $this;
    }

    /**
     * @param Community $community
     * @return UserCommunitySetting|null
     */
    public function getSettingsByCommunity(Community $community)
    {
        return $this->getUserCommunitySettings()->filter(function (UserCommunitySetting $setting) use ($community) {
            return $setting->getCommunity()->getId() == $community->getId();
        })->first();
    }

    /**
     * @param Community $community
     * @return mixed
     */
    public function getBalanceByCommunity(Community $community)
    {
        return $this->getUserCommunityBalances()->filter(function (UserCommunityBalance $balance) use ($community) {
            return $balance->getCommunity()->getId() == $community->getId();
        })->first();
    }

    /**
     * @return Collection|Review[]
     */
    public function getMeReviews(): Collection
    {
        return $this->me_reviews;
    }

    public function addMeReview(Review $meReview): self
    {
        if (!$this->me_reviews->contains($meReview)) {
            $this->me_reviews[] = $meReview;
            $meReview->setRateTo($this);
        }

        return $this;
    }

    public function removeMeReview(Review $meReview): self
    {
        if ($this->me_reviews->removeElement($meReview)) {
            // set the owning side to null (unless already changed)
            if ($meReview->getRateTo() === $this) {
                $meReview->setRateTo(null);
            }
        }

        return $this;
    }

    public function getTempToken(): ?string
    {
        return $this->temp_token;
    }

    public function setTempToken(?string $temp_token): self
    {
        $this->temp_token = $temp_token;

        return $this;
    }

    /**
     * @return Collection|MetricOrder[]
     */
    public function getMetricOrders(): Collection
    {
        return $this->metricOrders;
    }

    public function addMetricOrder(MetricOrder $metricOrder): self
    {
        if (!$this->metricOrders->contains($metricOrder)) {
            $this->metricOrders[] = $metricOrder;
            $metricOrder->setUser($this);
        }

        return $this;
    }

    public function removeMetricOrder(MetricOrder $metricOrder): self
    {
        if ($this->metricOrders->removeElement($metricOrder)) {
            // set the owning side to null (unless already changed)
            if ($metricOrder->getUser() === $this) {
                $metricOrder->setUser(null);
            }
        }

        return $this;
    }

    public function getCallDates()
    {
        return $this->getCallItem()->map(function (CallUser $callUser) {
            return $callUser->getCallInstance()->getCreatedAt()->format('d.m.Y');
        });
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|UserAction[]
     */
    public function getUserActions(): Collection
    {
        return $this->userActions;
    }

    public function addUserAction(UserAction $userAction): self
    {
        if (!$this->userActions->contains($userAction)) {
            $this->userActions[] = $userAction;
            $userAction->setUser($this);
        }

        return $this;
    }

    public function removeUserAction(UserAction $userAction): self
    {
        if ($this->userActions->removeElement($userAction)) {
            // set the owning side to null (unless already changed)
            if ($userAction->getUser() === $this) {
                $userAction->setUser(null);
            }
        }

        return $this;
    }

    public function getInvitedToCommunity(): ?Community
    {
        return $this->invitedToCommunity;
    }

    public function setInvitedToCommunity(?Community $invitedToCommunity): self
    {
        $this->invitedToCommunity = $invitedToCommunity;

        return $this;
    }

    /**
     * @return Collection|Community[]
     */
    public function getManageCommunities(): Collection
    {
        return $this->manageCommunities;
    }

    public function addManageCommunity(Community $manageCommunity): self
    {
        if (!$this->manageCommunities->contains($manageCommunity)) {
            $this->manageCommunities[] = $manageCommunity;
        }

        return $this;
    }

    public function removeManageCommunity(Community $manageCommunity): self
    {
        $this->manageCommunities->removeElement($manageCommunity);

        return $this;
    }

    public function getCalendlyLink(): ?string
    {
        return $this->calendlyLink;
    }

    public function setCalendlyLink(?string $calendlyLink): self
    {
        $this->calendlyLink = $calendlyLink;

        return $this;
    }
}
