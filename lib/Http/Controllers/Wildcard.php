<?php

namespace Lernkarten\Http\Controllers;

use Context;
use Navigation;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PageLayout;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Wildcard
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $plugin = $this->container->get('plugin');
        $cid = Context::getId();
        $pluginUrl = $plugin->getPluginURL();

        if ($cid && !$plugin->isActivated($cid) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            return $response->withStatus(401);
        }

        Navigation::activateItem($cid ? '/course/lernkarten/index' : '/contents/lernkarten/index');
        PageLayout::setHelpKeyword('Lernkarten.Introduction');
        PageLayout::addStylesheet($pluginUrl . '/dist/style.css');
        PageLayout::addScript($pluginUrl . '/dist/lernkarten.js', ['type' => 'module']);

        $initialState = [
            'isTeacher' => Context::isCourse()
                ? $GLOBALS['perm']->have_studip_perm('tutor', Context::getId())
                : $GLOBALS['perm']->have_perm('tutor'),
            'wordLimit' => \Config::get()->getValue('LERNKARTEN_WORD_LIMIT')
        ];
        $response->getBody()->write($this->view($initialState));

        return $response;
    }

    /**
     * @param mixed $initialState
     */
    private function view($initialState): string
    {
        ob_start(); ?>
        <div id="lernkarten-app"></div>
        <script>
            document.addEventListener(
                "DOMContentLoaded",
                function() {
                    if (window.STUDIP && window.STUDIP.mountLernkarten) {
                        STUDIP.domReady(() => {
                            const initialState = <?= json_encode($initialState) ?>;
                            const vm = window.STUDIP.mountLernkarten("#lernkarten-app", initialState);
                        })
                    }
                }
            )
        </script>
        <?php
        $content = ob_get_clean() ?: '';
        return $GLOBALS['template_factory']->render('layouts/base', [
            'content_for_layout' => $content,
        ]);
    }
}
