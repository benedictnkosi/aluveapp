<?php

namespace App\Helpers\FormatHtml;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class InvoiceHTML
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }

    public function formatHtml($reservation): string
    {
        $html = '<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td class="title">
									<img src="/assets/images/logo.png" style="width: 100%; max-width: 300px" />
								</td>

								<td>
									<h1>INVOICE</h1>
									#{{reservation_id}}<br />
									Created: {{created}}<br />
									<b>Balance Due: R{{due}}.00</b>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>
								<td>
									Aluve Guesthouse<br />
									187 Kitchener Avenue<br />
									Kensington<br />
									Johannesburg<br />
									+27 79 634 7610<br />
								</td>

								<td>
									<b>Invoice To:</b><br />
									{{guest_name}}<br />
									{{guest_phone}}<br />
									{{guest_email}}
								</td>
							</tr>
						</table>
					</td>
				</tr>


				<tr class="heading">
					<td>Item</td>
					<td>Nights</td>
					<td>Rate</td>
					<td>Amount</td>
				</tr>

				<tr class="item">
					<td>{{room_name}}<br>
					{{check_in}}</td>
					<td>{{nights}}</td>
					<td>{{price_per_night}}</td>
					<td>{{total}}</td>
				</tr>

				<tr class="item">

				</tr>

				<tr class="item last">
				</tr>

				<tr class="total">
					<td></td>

					<td>Total: {{total}}</td>
				</tr>
			</table>';

        return $html;
    }
}