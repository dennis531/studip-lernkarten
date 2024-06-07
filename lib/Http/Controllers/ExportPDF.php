<?php

namespace Lernkarten\Http\Controllers;

use Lernkarten\Models\Card;
use Lernkarten\Models\Deck;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TCPDF;
use User;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class ExportPDF
{
    private $container;
    private $user;

    public function __construct(ContainerInterface $container, User $user)
    {
        $this->container = $container;
        $this->user = $user;
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
        $deck = Deck::find(+$args['id']);
        if (!$deck || !Deck::getPolicy()->view($this->user, $deck)) {
            return $response->withStatus(404);
        }

        $data = ['name' => $deck->name];
        $payload = json_encode($data);

        $pdf = $this->getPdfExporter($deck);

        $response->getBody()->write($pdf->Output('deck.pdf', 'S'));
        return $response->withHeader('Content-type', 'application/pdf');
    }

    private function getPdfExporter(Deck $deck): TCPDF
    {
        return new class ($deck) extends TCPDF {
            private $deck;

            public function __construct(Deck $deck)
            {
                parent::__construct('L', 'mm', 'A6', true, 'UTF-8', false);

                $this->deck = $deck;

                $this->setFont('dejavusans', '', 10);
                $this->setMargins(10, 15, 10);

                $this->setPrintHeader(false);
                $this->setPrintFooter(false);

                $this->SetAuthor($deck->owner->getFullName());
                $this->SetTitle(sprintf('Kartensatz: %s', $deck->name));
                $this->SetSubject($deck->description);

                $this->addFrontPage();
                foreach ($deck->cards as $index => $card) {
                    $this->addCard($index, $card);
                }
            }

            public function addFrontPage(): void
            {
                $this->AddPage();
                $this->writeHTMLCell(
                    0,
                    30,
                    0,
                    40,
                    sprintf(
                        '<h1 style="text-align:center;">%s</h1>',
                        sprintf(_('Kartensatz: %s'), $this->deck->name)
                    )
                );
                $this->AddPage();
                $this->writeHTML(
                    sprintf(
                        '<p style="font-weight: bold;">%s</p><p>%s</p>',
                        _('Beschreibung'),
                        $this->deck->description ?: '-'
                    )
                );
                $this->writeHTML(
                    sprintf(
                        '<br/><p style="font-weight: bold;">%s</p><p>%s</p>',
                        _('Metadaten'),
                        $this->deck->metadata ?: '-'
                    )
                );
            }

            public function addCard(int $index, Card $card): void
            {
                $fields = $card->getFields();
                $this->AddPage();
                $this->writeHTML($fields['front']);
                $this->setXY(5, 5);
                $this->setFontSize(8);
                $this->Cell(
                    0,
                    0,
                    sprintf(_('Kartensatz %s – Karte %d'), $this->deck->name, $index + 1),
                    0,
                    2,
                    'R'
                );
                $this->setFontSize(10);
                $this->AddPage();
                $this->writeHTML($fields['back']);
            }
        };
    }
}
