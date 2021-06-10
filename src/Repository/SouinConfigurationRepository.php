<?php

declare(strict_types=1);

namespace PrestaShop\Module\Souin\Repository;

use Doctrine\ORM\EntityRepository;
use PrestaShop\Module\Souin\Entity\SouinConfiguration;

/**
 * Class SupplierExtraImageRepository
 * @package PrestaShop\Module\Souin\Repository
 */
class SouinConfigurationRepository extends EntityRepository
{
    public function upsertSupplierImageName()
    {
        /** @var SouinConfiguration $souin */
        $souin = $this->find(1);
        if (!$souin) {
            $souin = new SouinConfiguration();
        }
        $em = $this->getEntityManager();
        $em->persist($souin);
        $em->flush();
    }

    public function deleteExtraImage(SouinConfiguration $souinConfiguration)
    {
        $em = $this->getEntityManager();
        if ($souinConfiguration) {
            $em->remove($souinConfiguration);
            $em->flush();
        }
    }
}
