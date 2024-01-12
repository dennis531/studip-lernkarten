<?php

namespace Lernkarten\Models;

use InvalidArgumentException;
use LernkartenPlugin;
use PluginManager;
use Range;

class Instance
{
    use HasPolicy;

    /**
     * @param Range $range
     * @return ?static
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function findForRange(Range $range)
    {
        switch ($range->getRangeType()) {
            case 'course':
            case 'user':
                $pluginManager = PluginManager::getInstance();
                $pluginInfo = $pluginManager->getPluginInfo(LernkartenPlugin::class);
                $activated = $pluginManager->isPluginActivated($pluginInfo['id'], $range->getRangeId());
                return $activated ? new self($range) : null;
        }

        throw new InvalidArgumentException('Only ranges of type "user" and "course" are supported.');
    }


    /**
     * @var Range
     */
    private $range;

    /**
     * Create a new representation of a LernkartenPlugin instance.
     *
     * This model class purely represents and does not create anything.
     * Its purpose is to have all things related to a
     * single LernkartenPlugin instance in one place.
     *
     * @param Range $range the range of this LernkartenPlugin instance
     */
    public function __construct(Range $range)
    {
        $this->range = $range;
    }

    /**
     * Returns the range of this LernkartenPlugin instance.
     *
     * @return Range the course of this instance
     */
    public function getRange(): Range
    {
        return $this->range;
    }
}
