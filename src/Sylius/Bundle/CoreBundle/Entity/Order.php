<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\UserInterface;
use Sylius\Bundle\AddressingBundle\Model\AddressInterface;
use Sylius\Bundle\CoreBundle\Model\OrderInterface;
use Sylius\Bundle\CoreBundle\Model\InventoryUnitInterface;
use Sylius\Bundle\SalesBundle\Entity\Order as BaseOrder;
use Sylius\Bundle\SalesBundle\Model\AdjustmentInterface;
use Sylius\Bundle\ShippingBundle\Model\ShipmentInterface;

/**
 * Order entity.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class Order extends BaseOrder implements OrderInterface
{
    /**
     * User.
     *
     * @var UserInterface
     */
     protected $user;

    /**
     * Order shipping address.
     *
     * @var AddressInterface
     */
    protected $shippingAddress;

    /**
     * Order billing address.
     *
     * @var AddressInterface
     */
    protected $billingAddress;

    /**
     * Shipments for this order.
     *
     * @var Collection
     */
    protected $shipments;

    /**
     * Inventory units.
     *
     * @var Collection
     */
    protected $inventoryUnits;

    /**
     * Currency ISO code.
     *
     * @var string
     */
    protected $currency;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->inventoryUnits = new ArrayCollection();
        $this->shipments = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddress(AddressInterface $address)
    {
        $this->shippingAddress = $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddress(AddressInterface $address)
    {
        $this->billingAddress = $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxTotal()
    {
        $taxTotal = 0;

        foreach ($this->getTaxAdjustments() as $adjustment) {
            $taxTotal += $adjustment->getAmount();
        }

        return $taxTotal;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxAdjustments()
    {
        return $this->adjustments->filter(function (AdjustmentInterface $adjustment) {
            return Order::TAX_ADJUSTMENT === $adjustment->getLabel();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeTaxAdjustments()
    {
        foreach ($this->getTaxAdjustments() as $adjustment) {
            $this->removeAdjustment($adjustment);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPromotionTotal()
    {
        $promotionTotal = 0;

        foreach ($this->getPromotionAdjustments() as $adjustment) {
            $promotionTotal += $adjustment->getAmount();
        }

        return $promotionTotal;
    }

    /**
     * {@inheritdoc}
     */
    public function getPromotionAdjustments()
    {
        return $this->adjustments->filter(function (AdjustmentInterface $adjustment) {
            return Order::PROMOTION_ADJUSTMENT === $adjustment->getLabel();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removePromotionAdjustments()
    {
        foreach ($this->getPromotionAdjustments() as $adjustment) {
            $this->removeAdjustment($adjustment);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingTotal()
    {
        $shippingTotal = 0;

        foreach ($this->getShippingAdjustments() as $adjustment) {
            $shippingTotal += $adjustment->getAmount();
        }

        return $shippingTotal;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAdjustments()
    {
        return $this->adjustments->filter(function (AdjustmentInterface $adjustment) {
            return Order::SHIPPING_ADJUSTMENT === $adjustment->getLabel();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeShippingAdjustments()
    {
        foreach ($this->getShippingAdjustments() as $adjustment) {
            $this->removeAdjustment($adjustment);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInventoryUnits()
    {
        return $this->inventoryUnits;
    }

    /**
     * {@inheritdoc}
     */
    public function addInventoryUnit(InventoryUnitInterface $unit)
    {
        if (!$this->inventoryUnits->contains($unit)) {
            $unit->setOrder($this);
            $this->inventoryUnits->add($unit);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeInventoryUnit(InventoryUnitInterface $unit)
    {
        if ($this->inventoryUnits->contains($unit)) {
            $unit->setOrder(null);
            $this->inventoryUnits->removeElement($unit);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * {@inheritdoc}
     */
    public function addShipment(ShipmentInterface $shipment)
    {
        if (!$this->hasShipment($shipment)) {
            $shipment->setOrder($this);
            $this->shipments->add($shipment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeShipment(ShipmentInterface $shipment)
    {
        if ($this->hasShipment($shipment)) {
            $shipment->setOrder(null);
            $this->shipments->removeElement($shipment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasShipment(ShipmentInterface $shipment)
    {
        return $this->shipments->contains($shipment);
    }

    /**
     * {@inheritdoc}
     */
    public function getPromotionCoupon()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPromotionSubjectItemTotal()
    {
        return $this->getTotal();
    }

    /**
     * {@inheritdoc}
     */
    public function getPromotionSubjectItemCount()
    {
        return $this->items->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
