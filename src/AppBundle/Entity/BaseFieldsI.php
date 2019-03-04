<?php
declare(strict_types=1);

namespace AppBundle\Entity;

interface BaseFieldsI
{
    public function setDeleted($deleted);
    public function getDeleted();

    public function setCreatedDate($createdDate);
    public function getCreatedDate();

    public function setUpdatedDate($updatedDate);
    public function getUpdatedDate();
}