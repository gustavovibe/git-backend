<html>
<head>
	<title>Booking Confirmation</title>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td align="left" style="max-width: 203px;" valign="middle"><img alt="" border="0" class="w203px" src="https://vibeadventures.be/images//logo.png" style="display: block; max-width: 203px; width: 100%;" width="203" /></td>
			<td style="width:20px">&nbsp;</td>
			<td align="right"><a href="{{ rtrim(config('frontend.url'), '/') }}/my-trips/order?order_id={{$order->booking_id}}" target="_blank" style="background-color:#ff6c0e;font-size:14px;font-weight:bold;line-height:37px;width:169px;color:#ffffff;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;" >Manage my booking</a><br />
			<span style="font-family: Canaro, sans-serif;font-size: 12px;color: #000000;padding-top: 5px;display: block;">If you need help, <a style="color: #82cf45; font-weight: bold;" href="{{ rtrim(config('frontend.url'), '/') }}/contact" target="_blank">contact us</a></span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td align="left" bgcolor="#ffffff" style="padding: 30px;" valign="top">
			<div align="center" style="padding: 20px 0px;" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 28px; color: #000000;line-height: 34px;"><span style="font-family: Canaro, sans-serif; font-size: 28px; color: #000000;line-height: 34px;">
@if ($order->booking_status == 'pending') 
    <strong>We're <span color="#FF6C0E">confirming</span> your booking</strong>
@else 
    <strong>Your booking is <span color="#82CF45">confirmed</span></strong>
@endif
</span></div>
			</td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td align="center" valign="top">
			<table border="0" cellpadding="0" cellspacing="0" style="text-align: center;">
				<tbody>
					<tr>
						<td style="font-family: Arial, sans-serif; font-weight: bold; font-size: 12px; color: #4f4f4f; line-height: 20px; text-transform: uppercase;">Booking number</td>
					</tr>
					<tr>
						<td style="font-family: Arial, sans-serif; font-weight: bold; font-size: 16px; color: #000000; line-height: 20px; letter-spacing: 3px;">{{$order->booking_id}}</td>
					</tr>
				</tbody>
			</table>
			</td>
			<td align="center" valign="top">
			<table border="0" cellpadding="0" cellspacing="0" style="text-align: center;">
				<tbody>
					<tr>
						<td style="font-family: Arial, sans-serif; font-weight: bold; font-size: 12px; color: #4f4f4f; line-height: 20px; text-transform: uppercase;">Booking status</td>
					</tr>
					<tr>
						<td align="center">
						@if ($order->booking_status !='confirmed')
						<table border="0" cellpadding="0" cellspacing="0" style="background: rgb(255,108,14,0.25); border-radius: 20px; width: 100px;">
							<tbody>
								<tr>
									<td align="center" style="padding: 2px 4px 2px 4px;"><img alt="" height="18" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/sync.png" style="display: block;" width="18" /></td>
									<td style="font-family: Arial, sans-serif; font-size: 12px; color: #FF6C0E; font-weight: bold; text-decoration: none;">Pending</td>
								</tr>
							</tbody>
						</table>	
						@else
						<table border="0" cellpadding="0" cellspacing="0" style="background: #82cf45; border-radius: 20px; width: 100px;">
							<tbody>
								<tr>
									<td align="center" style="padding: 2px 4px 2px 4px;"><img alt="" height="18" src="https://blog.vibeadventures.com/wp-content/uploads/2025/06/tik.png" style="display: block;" width="18" /></td>
									<td style="font-family: Arial, sans-serif; font-size: 12px; color: #ffffff; font-weight: bold; text-decoration: none;">Confirmed</td>
								</tr>
							</tbody>
						</table>
						@endif
						</td>
					</tr>
				</tbody>
</table>
			</td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 25px auto;width: 100%;text-align: center;">
	<tbody>
		<tr>
			<td align="center" valign="top">
			<p style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">
				<span style="font-weight: bold; text-decoration: none;">{{ $order->user->name }}</span> 
				<span style="font-weight: normal; text-decoration: none;">, thank you for choosing Vibe Adventures!<br>  
Below is your booking summary, answers to FAQs, and links to download your flight tickets, itinerary, and invoice.<br>
Detailed trip notes—including daily activities, accommodation list, and your tour leader’s contact info—will be emailed 1–4 weeks before departure (timing may vary by tour operator).<br>
In the meantime, if you have any questions, feel free to <a style="color: #82cf45; font-weight: bold; text-decoration: none;" href="{{ rtrim(config('frontend.url'), '/') }}/contact">contact us</a>.</span> 
			</p></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 0 auto;width: 100%;text-align: center;">
	<tbody>
		<tr>
			<td>
			@if ($order->booking_status == 'pending') 	
				<span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;font-weight: normal; text-decoration: none;">
					Your reservation is 
					<strong style="color: #82cf45;">complete</strong> 
				</span>	
			@else
				<span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;font-weight: normal; text-decoration: none;">
				We’ve received your payment and are confirming your booking with the tour operator (your flights are reserved). This process can take up to 72 hours. We’ll send your final booking confirmation and e-tickets as soon as possible.
				</span>
			@endif
			</td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:20px auto auto auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td style="width: 35px;"><img alt="" border="0" height="24" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1886082545.png" style="display: block;" width="24" /></td>
			<td><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">Payment</span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 0 auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Your payment @if ($order->booking_status !='pending') <strong>is being processed</strong>@else <strong>was successfully processed</strong>@endif.</span></td>
		</tr>
	</tbody>
</table>

@if ($order->booking_status !='pending')
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:10px auto auto auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td style="width: 35px;"><img alt="" border="0" height="24" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1886082545.png" style="display: block;" width="24" /></td>
			<td><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">Final booking confirmation</span></td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 0 auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Both the tour operator and airline carriers have confirmed your trip&nbsp;— you’re all set to travel!</span></td>
		</tr>
	</tbody>
</table>
@else
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:10px auto auto auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td style="width: 35px;"><img alt="" border="0" height="24" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/pending.png" style="display: block;" width="24" /></td>
			<td><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">Booking with the tour operator.</span></td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 0 auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td>
				<span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">
					We’re confirming your booking with the tour operator. Once complete, you’ll receive your final confirmation and be all set!
				</span>
			</td>
		</tr>
	</tbody>
</table>
@endif
@if ($order->booking_status !='pending')
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:10px auto auto auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td style="width: 35px;"><img alt="" border="0" height="24" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-603419551.png" style="display: block;" width="24" /></td>
			<td><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">Get ready for the trip</span></td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 0 auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td>
			<ul>
				<li><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Book Pre- and Post-Tour Accommodation<span style="font-weight: normal; text-decoration: none;">: These are not included in the organized adventure. You can </span><a style="color: #82cf45; text-decoration: none;" href="https://trip.tp.st/CGLH4d7u" target="_blank">book them now</span><span style="text-decoration: none;"> or wait for your trip notes (usually sent 2–4 weeks before departure) to book the same hotels where the adventure starts and ends. </span></span></li>
				<li><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Check-in with the Airlines: Make sure to complete your check-in online (recommended) or at the airport before your flight. </span></li>
			</ul>
			</td>
		</tr>
	</tbody>
</table>
@endif

<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate;height: 15px; line-height: 15px; font-size: 13px;width:100%">
	<tbody>
		<tr>
			<td align="center" style="border-radius: 3px;" valign="middle">
			<div style="overflow: hidden; border-radius: 3px;"><a style="background-color:#ff6c0e;font-size:14px;font-weight:bold;line-height:37px;width:169px;color:#ffffff;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;" href="{{ rtrim(config('frontend.url'), '/') }}/my-trips/order?order_id={{$order->booking_id}}" target="_blank">View booking</a>

			<div style="height: 10px; line-height: 10px; font-size: 8px;">&nbsp;</div>

			<div>
			<div style="line-height: 17px;"><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;"><span style="text-decoration: none;">If you need help, </span><a style="color: #82cf45; font-weight: bold; text-decoration: none;" href="{{ rtrim(config('frontend.url'), '/') }}/contact">contact us</a></span></div>
			</div>
			</div>
			</td>
		</tr>
	</tbody>
</table>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<table border="0" cellpadding="0" cellspacing="0" style="padding: 20px 30px; border-radius: 20px; border-width: 1px; border-color: #82cf45; border-style: dotted;border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
	<tbody>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Frequently Asked Questions</span></td>
		</tr>
		<tr height="20" style="height: 20px;">
		</tr>
		@if ($order->booking_status !='pending')
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">Why doesn't my trip include pre- and post-tour accommodations?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">Since our trips combine organized adventures with flights from independent suppliers, we book flights one day before and one day after the adventure. This ensures smooth logistics, gives travelers time to rest between the adventure and flights, and offers extra time to explore the destination independently.</span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">How do I check in for my flight?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px;">You can check in online through the airline's website using your name and booking number (found on your e-ticket) or at the airport check-in counter. Make sure to download your e-ticket and have it ready for check-in.</span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">Where can I find baggage and check-in policies?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">Review the conditions outlined by each airline carrier in your flight summary before your trip. </span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">Are the accommodations guaranteed as listed in the tour description or trip notes?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">Accommodations are approximate and may change based on availability, group size, and other factors. If changes occur, accommodations of a similar category will be provided.</span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">What happens if the weather impacts my scheduled activities?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">In case of unfavorable weather or other valid reasons, the sequence and duration of activities may be modified or canceled without prior notice.</span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">Do I need a visa for my trip?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">Check visa requirements for the country in your adventure itinerary and flight summary. Don’t forget to check if you need a transit visa as well.</span></td>
		</tr>
		@else
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">
				When will I get the final booking confirmation?
			</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">		
			You’ll receive the final booking confirmation as soon as we get it from the tour operator, as we don’t operate the adventures ourselves. Since we gather data from multiple tour operators to offer you the best selection and prices, our booking process is more complex. Most bookings are confirmed immediately, but occasionally, it may take up to 72 hours. Rest assured, we prioritize bookings to ensure everyone can travel as planned.			
			</span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">
				What happens to my money?
			</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">		
			We’ve held the necessary funds for your booking to secure the flights and adventure, but the money remains with your bank and won’t be charged until the booking is confirmed. If we’re unable to confirm your booking within 72 hours, it will be automatically canceled, and your request fully refunded.			
			</span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		@endif
	</tbody>
</table>

@if ($order->tour)

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;margin-top:25px">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Adventure summary</span></td>
			<td align="right" valign="middle">
			@if ($order->booking_status == 'confirmed') 	
				<a href="https://vibeadventures.be/api/boooking-summary-pdf?tour_id={{ $order->tour_id }}" style="font-size:14px;font-weight:bold;line-height:31px;width:171px;border: 1px solid #ff6c0e;color:#ff6c0e;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;" target="_blank">Download itinerary</a>
			@endif	
			</td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="padding: 20px 30px; border-radius: 20px; border-width: 1px; border-color: #82cf45;border-style: solid; border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
	<tbody>
		<tr>
			<td align="left" style="width:150px;padding-right: 20px;" valign="middle">
				 @if ($order->tour->main_thumbnail)
				<img src="{{ $order->tour->main_thumbnail }}" style="height:150px;" />
				@endif
			</td>
			<td align="left" valign="top">
			<table>
				<tbody>
					<tr>
						<td><span style="font-family: Canaro, sans-serif; font-weight: bold; font-size: 15px; color: #82cf45;">{{ $order->tour->tour_name }}</span></td>
					</tr>
				</tbody>
			</table>

			<table>
				<tbody>
					<tr>
						<td width="24"><img border="0" height="18" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i196401944.png" style="display: block;" width="18" /></td>
						<td width="30"><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #82cf45;">{{ $order->tour->ratings_overall }}</span></td>
						<td><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 12px; color: #9ca3af;">{{ $order->tour->reviews_count }} reviews </span></td>
					</tr>
				</tbody>
			</table>

			<table>
				<tbody>
					<tr>
						<td width="28"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1337963125.png" style="height: 26px;width: 26px;" /></td>
						<td style="min-width: 50px;"><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 12px; color: #000000;">Starts in: </span></td>
						<td style="min-width: 76px;"><span style="color: #9ca3af;font-family: 'Interstate Light Cond', sans-serif; font-size: 12px;">{{ $order->start_city . ',' . $order->origin }}</span></td>
						<td width="28"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1337963125.png" style="height: 26px;width: 26px;" /></td>
						<td style="min-width: 50px;"><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 12px; color: #000000;">Ends in: </span></td>
						<td style="min-width: 76px;"><span style="color: #9ca3af;font-family: 'Interstate Light Cond', sans-serif; font-size: 12px;">{{ $order->end_city . ',' . $order->f_destination }}</span></td>
					</tr>
				</tbody>
			</table>

			<table>
				<tbody>
					<tr>
						<td width="30"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1067799321.png" style="margin-left:-3px;height: 30px;width: 30px;" /></td>
						<td style="min-width: 50px;"><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 12px; color: #000000;">Starts on:</span></td>
						<td style="min-width: 72px;"><span style="color: #9ca3af;font-family: 'Interstate Light Cond', sans-serif; font-size: 12px;">{{ \Carbon\Carbon::parse($order->start)->format('M d, Y') }}</span></td>
						<td width="32"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i64381891.png" style="margin-left:1px;height: 28px;width: 28px;" /></td>
						<td style="min-width: 50px;"><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 12px; color: #000000;">Ends on:</span></td>
						<td style="min-width: 72px;"><span style="color: #9ca3af;font-family: 'Interstate Light Cond', sans-serif; font-size: 12px;">{{ \Carbon\Carbon::parse($order->end)->format('M d, Y') }}</span></td>
					</tr>
				</tbody>
			</table>

			<table>
				<tbody>
					<tr>
						<td width="30"><img border="0" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i751044998.png" style="margin-left:-1px;height: 28px;width: 28px;" /></td>
						<td style="min-width: 50px;"><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 12px; color: #000000;">Rooms:</span></td>
						<td><span style="color: #9ca3af;font-family: 'Interstate Light Cond', sans-serif; font-size: 12px;">
							 @foreach ($order['passengers'] as $acc)
                                        <p>
                                            <span style="color: #82CF45;">
                                                {{ $acc['passengers'] }}
                                            </span>
                                            × {{ $acc['name'] }}
                                        </p>
                                    @endforeach
						</span></td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
@endif
@if (isset($order->attempt->duffel_res['data']['slices']))

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;margin-top:25px">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Flights summary</span></td>
			@if ($order->booking_status == 'confirmed')
			<td align="right" valign="middle"><a href="https://vibeadventures.be/api/get-tickets?orderId={{ $order->duffel_id }}" style="font-size:14px;font-weight:bold;line-height:31px;width:171px;border: 1px solid #ff6c0e;color:#ff6c0e;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;" target="_blank">Download tickets</a></td>
			@endif
		</tr>
	</tbody>
</table>
<div border="0" cellpadding="0" cellspacing="0" style="border-radius: 20px; border-width: 1px; border-color: #82cf45;border-style: solid; border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
<div style="padding: 20px 30px;">
@foreach ($order->attempt->duffel_res['data']['slices'] as $or)	
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 18px; color: #4f4f4f;">
			@if(isset($or['segments'][0]['origin']['city']['name'])  )
				<b>{{ $or['segments'][0]['origin']['city']['name'] }}</b>
				@else 
				<b>{{ $or['segments'][0]['origin']['city_name'] }}</b>
				@endif
				 → 
				@if(isset($or['segments'][0]['destination']['city_name'])  )
				<b>{{ $or['segments'][0]['destination']['city_name'] }}</b>
				@else 
				<b>{{ $or['segments'][0]['destination']['city']['name'] }}</b>
				@endif
			</span></td>
			<td align="right" valign="middle"><span style="font-family: 'Segoe UI', sans-serif; font-weight: bold; font-size: 14px; color: #000000;">{{ \Carbon\CarbonInterval::make($or['duration'])->format('%hh %im') }}</span></td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;padding:10px;margin-top:20px">
	<tbody>
		<tr>
			<td align="left" valign="middle">
				<span style="font-family: Canaro, sans-serif; font-size: 18px; color: #000000;">{{ \Carbon\Carbon::parse($or['segments'][0]['departing_at'])->format('H:i') }}</span><br />
				<span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">{{ \Carbon\Carbon::parse($or['segments'][0]['departing_at'])->format('M d, Y') }}</span>
			</td>
			<td align="right" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #82cf45;">
				@if(isset($or['segments'][0]['origin']['city']['name'])  )
				<b>{{ $or['segments'][0]['origin']['city']['name'] }}@endif</b>
				({{$or['segments'][0]['origin']['iata_code']}})· </span><span style="font-family: Canaro, sans-serif; color: #4f4f4f; text-decoration: none;">{{$or['segments'][0]['origin']['name']}}</span></td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;margin-top:0px;margin-bottom:10px;padding-left:10px;padding-right: 10px;">
	<tbody>
		<tr>
			<td align="left" valign="middle">
			<span style="font-family: Canaro, sans-serif; font-size: 14px; color: #82cf45;">
				{{ \Carbon\CarbonInterval::make($or['duration'])->format('%hh %im') }}
			</span></td>
			<td align="right" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 14px;"><strong>{{$or['segments'][0]['operating_carrier']['name']}}</strong> {{$or['segments'][0]['operating_carrier_flight_number']}}</span></td>
		</tr>
	</tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;padding:10px;margin-top:20px">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 18px; color: #000000;">{{ \Carbon\Carbon::parse($or['segments'][0]['arriving_at'])->format('H:i') }}</span><br />
			<span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">{{ \Carbon\Carbon::parse($or['segments'][0]['arriving_at'])->format('M d, Y') }}</span></td>
			<td align="right" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #82cf45;">
				@if(isset($or['segments'][0]['destination']['city']['name'])  )
				<b>{{ $or['segments'][0]['destination']['city']['name'] }}@endif</b>
				({{$or['segments'][0]['destination']['iata_code']}})· </span><span style="font-family: Canaro, sans-serif; color: #4f4f4f; text-decoration: none;">{{$or['segments'][0]['destination']['name']}}</span></td>
		</tr>
	</tbody>
</table>

{{-- === HERE: After the very first slice, inject your extra table === --}}
    @if ($loop->first)
        <table border="0" cellpadding="0" cellspacing="0" style="width:100%;margin-top:20px;margin-bottom:20px;padding-left:10px;padding-right: 10px;">
            <tbody>
                <tr>
                    <td align="center" valign="middle">
                        <a href="#"
                           style="font-size:14px;font-weight:bold;line-height:31px;width:171px;
                                  border: 1px dotted #ff6c0e;color:#ff6c0e;border-radius:10px;
                                  display:inline-block;font-family:Canaro, sans-serif;
                                  text-align:center;text-decoration:none;
                                  -webkit-text-size-adjust:none;box-sizing:border-box;"
                           target="_blank">
                            {{ $order->tour_length }} days in destination
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
@endforeach
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;margin-top:10px;padding-left:10px;padding-right: 10px;display: none;">
	<tbody>
		<tr>
			<td align="center" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000; text-decoration: none;"><strong>5h 55m </strong>layover</span></td>
		</tr>
	</tbody>
</table>
</div>
</div>
@endif
@if ($order->booking_status !='pending')
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;margin-top:25px">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Payment</span></td>
			<td align="right" valign="middle"><a href="https://vibeadventures.be/api/preview/invoice?booking_id={{$order->booking_id}}&orderId={{$order->duffel_id}}&payment_id={{$order->payment_id}}" style="font-size:14px;font-weight:bold;line-height:31px;width:171px;border: 1px solid #ff6c0e;color:#ff6c0e;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;" target="_blank">Download invoice</a></td>
		</tr>
	</tbody>
</table>
<div border="0" cellpadding="0" cellspacing="0" style="border-radius: 20px; border-width: 1px; border-color: #82cf45;border-style: solid; border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
<div style="padding: 20px 30px;">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 22px; color: #000000;">Total</span></td>
			<td align="right" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 22px; color: #000000;">${{ number_format( ceil($order->paid), 2 ) }} USD</span></td>
		</tr>
		<tr style="height:5px">
		</tr>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #4f4f4f;">Incl. taxes and fees</span></td>
			<td align="right" valign="middle"><a href="#" style="font-size:14px;font-weight:bold;line-height: 18px;width: fit-content;border: 1px dotted #ff6c0e;color:#ff6c0e;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 0px 10px;display:none" target="_blank">$100 USD OFF</a></td>
		</tr>
		<tr style="height:10px">
		</tr>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 18px; color: #000000;margin-left:10px">Flights</span></td>
			<td align="right" valign="middle"><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 18px; color: #82cf45;">Included</span></td>
		</tr>
		<tr style="height:10px">
		</tr>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 18px; color: #000000;margin-left:10px">Organized Adventure</span></td>
			<td align="right" valign="middle"><span style="font-family: 'Interstate Light Cond', sans-serif; font-size: 18px; color: #82cf45;">Included</span></td>
		</tr>
		<tr style="height:10px">
		</tr>
		<tr>
			<td align="left" valign="middle">&nbsp;</td>
			<td align="right" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 14px;font-style:italic">{{ \Carbon\Carbon::parse($order->start)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($order->end)->format('M d, Y') }}</span></td>
		</tr>
		<tr>
			<td align="left" valign="middle">&nbsp;</td>
			<td align="right" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 14px;font-style:italic">
				 @foreach ($order['passengers'] as $acc)
                                        <p>
                                            <span style="color: #82CF45;">
                                                {{ $acc['passengers'] }}
                                            </span>
                                            × {{ $acc['name'] }}
                                        </p>
                                    @endforeach
			</span>
			</td>
		</tr>
		<tr style="height:10px">
		</tr>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 20px; color: #000000;">Payment history</span></td>
			<td align="right" valign="middle">&nbsp;</td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="width:100%;margin-top:10px">
	<tbody>
		<tr>
			<th align="center" valign="middle"><a href="#" style="font-size:14px;line-height: 18px;width: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Date and time</a></th>
			<th align="center" valign="middle"><a href="#" style="font-size:14px;line-height: 18px;width: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Payment method</a></th>
			<th align="center" valign="middle"><a href="#" style="font-size:14px;line-height: 18px;width: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Payment status</a></th>
			<th align="center" valign="middle"><a href="#" style="font-size:14px;line-height: 18px;width: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Amount</a></th>
		</tr>
		<tr style="height:10px">
		</tr>
		<tr>
			<td align="center" valign="middle"><span style="font-family: Inter, sans-serif; font-size: 13px; color: #000000;">{{ \Carbon\Carbon::parse($order->stripe_created)->format('M d, Y') }}</span></td>
			<td align="center" valign="middle"><span style="font-family: Inter, sans-serif; font-size: 13px; color: #000000;">@if ($order->last_4)<strong>Visa</strong>****{{$order->last_4}}@else<strong>{{$order->payment_method}}</strong>@endif</span></td>
			@if ($order->booking_status !='pending')
			<td align="center" valign="middle"><span style="font-family: Inter, sans-serif;font-size: 13px;color: #82cf45;background: #def9cb;padding: 2px 8px;border-radius: 4px;border: 1px solid #82cf45;font-weight: bold;">Pending ✔</span></td>
			@else
			<td align="center" valign="middle"><span style="font-family: Inter, sans-serif;font-size: 13px;color: #82cf45;background: #def9cb;padding: 2px 8px;border-radius: 4px;border: 1px solid #82cf45;font-weight: bold;">Succeeded ✔</span></td>
<td align="center" valign="middle"><span style="font-family: Inter, sans-serif; font-size: 13px; color: #000000; font-weight: bold;">${{ number_format( ceil($order->paid * 1.15), 2 ) }} USD</span></td>
@endif
		</tr>
	</tbody>
</table>
</div>
</div>
@endif
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;margin-top:25px">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Travelers</span></td>
		</tr>
	</tbody>
</table>
@if ($order->travelers)
<div border="0" cellpadding="0" cellspacing="0" style="border-radius: 20px; border-width: 1px; border-color: #82cf45;border-style: solid; border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
<div style="padding: 20px 30px;">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
	<tbody>
		@foreach ($order->travelers as $traveler)
		<tr>
			<td align="left" valign="middle"><img alt="" border="0" class="w24px" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i1682235450.png" style="max-width: 24px; width: 100%;" width="24" /> <span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">{{ $traveler->title }} <b>{{ $traveler->name.' '.$traveler->last }}<b></span></td>
			<td align="right" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">{{ \Carbon\Carbon::parse($traveler->birth)->format('j M Y') }}</span></td>
		</tr>
		<tr style="height:10px">
		@endforeach	
	</tbody>
</table>
</div>
</div>
@endif
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;margin-top:25px;dispaly:none">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Your contacts</span></td>
		</tr>
	</tbody>
</table>

<div border="0" cellpadding="0" cellspacing="0" style="border-radius: 20px; border-width: 1px; border-color: #82cf45;border-style: solid; border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
<div style="padding: 20px 30px;">
<table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
	<tbody>
		<tr>
			<td align="left" valign="middle"><img alt="" border="0" class="w24px" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i728805621.png" style="max-width: 24px; width: 100%;" width="24" /> 
			<span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">{{$order->user->phone}}</span>
			</td>
		</tr>
		<tr style="height:10px">
		</tr>
		<tr>
			<td align="left" valign="middle"><img alt="" border="0" class="w24px" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i1097817612.png" style="max-width: 24px; width: 100%;" width="24" /> 
			<span style="color: #000000; text-decoration: none;" target="_blank">{{$order->user->email}}</span>
			</td>
		</tr>
	</tbody>
</table>
</div>
</div>
@if ($order->booking_status == 'confirmed') 
<div border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;margin-top:25px;border-radius: 10px; border-width: 1px; border-color: #82cf45; border-style: dotted;">
 <div style="padding: 20px 30px;">
	<table>
		<tbody>
			<tr style="height:20px">
			</tr>
			<tr>
				<td align="center"><span style="font-family: Canaro, sans-serif; font-weight: bold; font-size: 25px; color: #000000;">Recommended</span></td>
			</tr>
			<tr style="height:10px">
			</tr>
			<tr>
				<td align="center"><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #4f4f4f;width:80%">Adding these services to your trip now can save you money compared to purchasing them later or in the destination.</span></td>
			</tr>
			<tr style="height:20px">
			</tr>
		</tbody>
	</table>
	<table style="width:100%">
		<tbody>
			<tr>
				<td align="center" bgcolor="#ffffff" height="132" style="padding: 10px; border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.11) 1.93642px 1.93642px 2.90463px;    width: 110px;" valign="middle"><img alt="" border="0" height="41" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i503421886.png" style="display: block;" width="73" /><br />
				<span style="font-family: Canaro, sans-serif; font-weight: bold; font-size: 11px; color: #000000;">Airport transfer</span><br />
				<span style="font-family: 'Interstate Light Cond', sans-serif;font-size: 8px;color: #000000;line-height: 10px;">not included in adventure? </span><br />
				<a href="https://www.welcomepickups.com/mexico-city/airport-transfer-book/?tap_a=134179-01b992&tap_s=4069286-0e3fe9 " style="background-color:#ff6c0e;font-size: 10px;width: fit-content;color:#ffffff;border-radius: 4px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;padding: 4px 10px;text-decoration: none;margin-top:10px" target="_blank">Get<br />
				Transfers <br></a></td>
				<td align="center" bgcolor="#ffffff" height="132" style="padding: 10px; border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.11) 1.93642px 1.93642px 2.90463px;    width: 110px;" valign="middle"><img alt="" border="0" height="41" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-312608904.png" style="display: block;" width="73" /><br />
				<span style="font-family: Canaro, sans-serif; font-weight: bold; font-size: 11px; color: #000000;">Insurance</span><br />
				<span style="font-family: 'Interstate Light Cond', sans-serif;font-size: 8px;color: #000000;line-height: 10px;">Available up to 24h before departure</span><br />
				<a href="https://visitorscoverage.com/?affid=ffe108ac09ad6&sub_id=b8f7c599386143409bc43f56a-436729" style="background-color:#ff6c0e;font-size: 10px;width: fit-content;color:#ffffff;border-radius: 4px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;padding: 4px 10px;text-decoration: none;margin-top:10px" target="_blank">Get<br />
				Insurance</a></td>
				<td align="center" bgcolor="#ffffff" height="132" style="padding: 10px; border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.11) 1.93642px 1.93642px 2.90463px;    width: 110px;" valign="middle"><img alt="" border="0" height="41" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-36888821.png" style="display: block;" width="73" /><br />
				<span style="font-family: Canaro, sans-serif; font-weight: bold; font-size: 11px; color: #000000;">Accommodation</span><br />
				<span style="font-family: 'Interstate Light Cond', sans-serif;font-size: 8px;color: #000000;line-height: 10px;">Need pre or post tour accommodation?</span><br />
				<a href="https://trip.tp.st/CGLH4d7u " style="background-color:#ff6c0e;font-size: 10px;width: fit-content;color:#ffffff;border-radius: 4px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;padding: 4px 10px;text-decoration: none;margin-top:10px" target="_blank">Book<br />
				accommodations</a></td>
				<td align="center" bgcolor="#ffffff" height="132" style="padding: 10px; border-radius: 5px; box-shadow: rgba(0, 0, 0, 0.11) 1.93642px 1.93642px 2.90463px;    width: 110px;" valign="middle"><img alt="" border="0" height="41" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i1821386128.png" style="display: block;" width="73" /><br />
				<span style="font-family: Canaro, sans-serif; font-weight: bold; font-size: 11px; color: #000000;">Activities</span><br />
				<span style="font-family: 'Interstate Light Cond', sans-serif;font-size: 8px;color: #000000;line-height: 10px;">Got extra days before or after the adventure?</span><br />
				<a href="https://www.viator.com/?pid=P00154312&uid=U00262192&mcid=58086&currency=EUR " style="background-color:#ff6c0e;font-size: 10px;width: fit-content;color:#ffffff;border-radius: 4px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;padding: 4px 10px;text-decoration: none;margin-top:10px" target="_blank">Find<br />
				activities</a></td>
			</tr>
		</tbody>
	</table>
 </div>
</div>
@endif
<div class="footer" style="margin-top:25px;">
<table style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i980061126.png" style="vertical-align: middle;width: 150px;" /></td>
			<td><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1150146922.png" style="vertical-align: middle;width: 150px;" /></td>
			<td style="text-align: right;">
                <table style="width: 100%;">
					<tr>
                		<td><a href="https://www.facebook.com/VibeAdventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/facebook.png" style="width:32px"/></a></td>
                		<td><a href="https://www.instagram.com/vibe.adventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/instagram.png" style="width:32px"/></a></td>
                		<td><a href="https://www.youtube.com/channel/UCQ9qyA-fVkdXarHBlzDCUBA?view_as=subscriber"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/youtube.png" style="width:32px"/></a></td>
                	</tr>
                	<tr>
                		<td><a href="https://www.tiktok.com/@vibeadventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/tiktok.png" style="width:32px"/></a></td>
                		<td><a href="https://www.pinterest.com/vibe_adventures/"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/pinterest.png" style="width:32px"/></a></td>
                		<td><a href="https://twitter.com/vibe_adventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/twitter.png" style="width:32px"/></a></td>
                	</tr>
                </table>
            </td>
		</tr>
	</tbody>
</table>

<table style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Ayuntamiento 115, Colonia Centro<br />
			<br />
			Cuauhtémoc, CDMX 06000 </span></td>
		</tr>
	</tbody>
</table>
</div>
</body>
</html>