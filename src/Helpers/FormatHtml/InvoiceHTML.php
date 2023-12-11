<?php

namespace App\Helpers\FormatHtml;

use App\Service\AddOnsApi;
use App\Service\PaymentApi;
use App\Service\PropertyApi;
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
        $reservation_id = $reservation->getId();
        $paymentApi = new PaymentApi($this->em, $this->logger);
        $propertyApi = new PropertyApi($this->em, $this->logger);
        $addOnsApi = new AddOnsApi($this->em, $this->logger);
        $totalPayment = $paymentApi->getReservationPaymentsTotal($reservation->getId());
        $invoiceTitle = 'INVOICE';
        if ($totalPayment > 0) {
            $invoiceTitle = 'RECIEPT';
        }
        $totalDue = $paymentApi->getTotalDue($reservation->getId());
        $totalDays = intval($reservation->getCheckIn()->diff($reservation->getCheckOut())->format('%a'));
        //property details
        $propertyDetails = $propertyApi->getPropertyDetails($reservation->getRoom()->getProperty()->getId());
        $totalPriceForAccommodation = (intVal($reservation->getRoom()->getPrice()) * $totalDays);
        $totalForAddOns = $addOnsApi->getAddOnsTotal($reservation_id);
        $totalForAll = intVal($totalPriceForAccommodation) + intVal($totalForAddOns);
        $totalPayments = $paymentApi->getReservationPaymentsTotal($reservation_id);

        $html = '<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="4">
						<table>
							<tr>
								
								<td>
									<h1>' . $invoiceTitle . '</h1>
									<h2>' . $propertyDetails[0]['name'] . '</h2>
									#' . $reservation->getId() . '<br />
									Created: ' . $reservation->getReceivedOn()->format('Y-m-d') . '<br />
									<b>Balance Due: R' . $totalDue . '.00</b>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="4">
						<table>
							<tr>
								<td>
									Name: ' . $propertyDetails[0]['name'] . '<br />
									Address:' . str_replace(",", "<br />", $propertyDetails[0]['address']) . '<br />
									Tel: ' . $propertyDetails[0]['phone_number'] . '<br />
									Email: ' . $propertyDetails[0]['email'] . '<br/>
								</td>
								<td>
									<b>Invoice To:</b><br />
									' . ucwords($reservation->getGuest()->getName()) . '<br />
									' . $reservation->getGuest()->getPhoneNumber() . '<br />
									' . $reservation->getGuest()->getEmail() . '
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="heading">
					<td>Item</td>
					<td>Quantity</td>
					<td>Price</td>
					<td>Amount</td>
				</tr>

				<tr class="item">
					<td>' . $reservation->getRoom()->getName() . '<br>
					Check-in: ' . $reservation->getCheckIn()->format('Y-m-d') . '<br>
						Check-out: ' . $reservation->getCheckOut()->format('Y-m-d') . '<br></td>
					<td>' . $totalDays . '</td>
					<td>R' . $reservation->getRoom()->getPrice() . '.00</td>
					<td>' . $totalPriceForAccommodation . '</td>
				</tr>

				<tr class="heading">
					<td>Add-ons</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>

				' . $addOnsApi->getAddOnsForInvoice($reservation_id) . '

				<tr class="total">
					<td></td>
					<td></td>
					<td><b>Total:</b> </td>
					<td><b>R' . number_format((float)$totalForAll, 2, '.', '') . '</b></td>
				</tr>

				<tr class="heading">
					<td>Payments</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>

				' . $paymentApi->getReservationPaymentsHtml($reservation_id) . '

				<tr class="total">
					<td></td>
					<td></td>
					<td><b>Total Payments:</b> </td>
					<td><b>' . number_format((float)$totalPayments, 2, '.', '') . '</b></td>
				</tr>

				<tr class="total">
					<td></td>
					<td></td>
					<td><b>Balance Due:</b> </td>
					<td><b>' . number_format((float)$paymentApi->getTotalDue($reservation_id), 2, '.', '') . '</b></td>
				</tr>

<tr >
					<td><b>Payment Method (Cashless Business. Card Payments Welcome)</b></td>
					<td class="no-border"></td>
					<td class="no-border"> </td>
					<td class="no-border"></td>
				</tr>
				<tr >
					<td><b>Please use Payshap as this reflects immediately. Reservations will only be confirmed once the money is in our account</b>
					</br>Payshap can be found on your banking app
					</br>Transfers from other banks to our bank (FNB) might take up to 3 working days. FNB to FNB can take from 0 to 2 hours.</td>
					<td class="no-border"></td>
					<td class="no-border"> </td>
					<td class="no-border"></td>
				</tr>
				<tr>
					<td>Payshap ID: 0796347610<br>
					<td class="no-border"><br></td>
					<td class="no-border"> </td>
					<td class="no-border"></td>
				</tr>
				<tr >
					<td><b>Banking Details:</b></td>
					<td class="no-border"></td>
					<td class="no-border"> </td>
					<td class="no-border"></td>
				</tr>
				<tr>
					<td>Bank Name:<br>
						Account Type:<br>
						Account Number:<br>
						Branch Code: <br></td>
					<td class="no-border">' . $propertyDetails[0]['bank_name'] . '<br>
						' . $propertyDetails[0]['bank_account_type'] . '<br>
						' . $propertyDetails[0]['bank_account_number'] . '<br>
						' . $propertyDetails[0]['bank_branch_number'] . '<br></td>
					<td class="no-border"> </td>
					<td class="no-border"></td>
				</tr>
				
				
				<tr >
					<td><b>Guesthouse Rules:</b><br></td>
					<td class="no-border"></td>
					<td class="no-border"> </td>
					<td class="no-border"></td>
				</tr>
				<tr>
					<td>ID or passport document required to check in<br>
					No noise at all times<br>
No loud music<br>
No parties<br>
No smoking inside the rooms<br>
No children under the age of 16<br>
<br>
<b>Cancellation:</b>
<br>
The guest can cancel free of charge until 7 days before arrival. The guest will be charged the total price of the reservation if they cancel in the 7 days before arrival. If the guest does not show up they will be charged the total price of the reservation.<br>
<br></td>
		
				</tr>
			</table>
		</div>';

        return $html;
    }

}