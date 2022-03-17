<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentaryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: CommentaryRepository::class)]
class Commentary
{
    // un trait est une sorte de class de PHP qui vous sert à réutiliser des propriétés et des setteurs et des getteurs.
    // Cela est utile lorsque vous avec plusieurs entités qui partagent des propriétés communes.

    # Pour utiliser ces deux classes PHP, il vous faudra 2 dépendances PHP de Gedmo : composer require gedmo/doctrine-extensions
    # timestamp : c'est une valeur numérique exprimée en secondes qui représente le temps écoulé (en seconde) depuis le 1er Janv. 1970 00:00


    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $Comment;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'article')]
    private $article;

    public function __construct()
    {
        $this->article = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->Comment;
    }

    public function setComment(string $Comment): self
    {
        $this->Comment = $Comment;

        return $this;
    }

    public function getArticle(): ?self
    {
        return $this->article;
    }

    public function setArticle(?self $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function addArticle(self $article): self
    {
        if (!$this->article->contains($article)) {
            $this->article[] = $article;
            $article->setArticle($this);
        }

        return $this;
    }

    public function removeArticle(self $article): self
    {
        if ($this->article->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getArticle() === $this) {
                $article->setArticle(null);
            }
        }

        return $this;
    }
}
