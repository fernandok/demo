<?php

namespace Drupal\cypress_custom_address\EventSubscriber;

use Drupal\profile\Event\ProfileLabelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactLabelSubscriber implements EventSubscriberInterface {

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        $events = [
            'profile.label' => 'onLabel',
        ];
        return $events;
    }

    /**
     * Sets the customer profile label to the first address line.
     *
     * @param \Drupal\profile\Event\ProfileLabelEvent $event
     *   The profile label event.
     */
    public function onLabel(ProfileLabelEvent $event) {
        /** @var \Drupal\profile\Entity\ProfileInterface $order */
        $profile = $event->getProfile();
        if ($profile->bundle() == 'customer' && !$profile->field_contact_address->isEmpty()) {
            $event->setLabel($profile->field_contact_address->address_line1);
        }
    }

}
