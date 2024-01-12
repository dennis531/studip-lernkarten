<?php

use Courseware\CoursewarePlugin;
use Lernkarten\Courseware\LernkartenBlock;

/**
 * @SuppressWarnings(StaticAccess)
 */
class CoursewareLernkartenBlockPlugin extends StudIPPlugin implements SystemPlugin, CoursewarePlugin
{
    public function __construct()
    {
        parent::__construct();

        require_once __DIR__ . '/vendor/autoload.php';

        if (match_route('dispatch.php/*/courseware')) {
            $this->addJavascript();
            $this->addStyles();
        }
    }

    private function addJavascript(): void
    {
        PageLayout::addScript($this->getPluginUrl() . '/courseware/dist/lernkarten-courseware.js', [
            'type' => 'module',
        ]);
        PageLayout::addScript($this->getPluginUrl() . '/dist/register.js', [
            'type' => 'module',
        ]);
    }

    private function addStyles(): void
    {
        // Add CSS to set the correct icon for the block in the block adder
        PageLayout::addStyle(
            '.cw-blockadder-item.cw-blockadder-item-lernkarten {
                 background-image:url(' .
                Icon::create('dialog-cards')->asImagePath() .
                ')
             }'
        );
    }

    /**
     * Implement this method to register more block types.
     *
     * You get the current list of block types and must return an updated list
     * containing your own block types.
     *
     * @param array $otherBlockTypes the current list of block types
     *
     * @return array the updated list of block types
     */
    public function registerBlockTypes(array $otherBlockTypes): array
    {
        $otherBlockTypes[] = LernkartenBlock::class;

        return $otherBlockTypes;
    }

    /**
     * Implement this method to register more container types.
     *
     * You get the current list of container types and must return an updated list
     * containing your own container types.
     *
     * @param array $otherContainerTypes the current list of container types
     *
     * @return array the updated list of container types
     */
    public function registerContainerTypes(array $otherContainerTypes): array
    {
        return $otherContainerTypes;
    }
}
