<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait statusTrait
{
    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $enabled;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $deleted;

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param mixed $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }




}