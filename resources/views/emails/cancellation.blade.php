<html>
<head>
	<title>Booking cancellation</title>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td align="left" style="max-width: 203px;" valign="middle"><img alt="" border="0" class="w203px" src="https://vibeadventures.be/images//logo.png" style="display: block; max-width: 203px; width: 100%;" width="203" /></td>
			<td style="width:20px">&nbsp;</td>
			<td align="right"><a href="#" style="background-color:#ff6c0e;font-size:14px;font-weight:bold;line-height:37px;width:169px;color:#ffffff;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;" target="_blank">Manage my booking</a><br />
			<span style="font-family: Canaro, sans-serif;font-size: 12px;color: #000000;padding-top: 5px;display: block;">If you need help, <a href="{{ rtrim(config('frontend.url'), '/') }}/contact" target="_blank" style="color: #82cf45; font-weight: bold;">contact us</span></span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td align="left" bgcolor="#ffffff" style="padding: 30px;" valign="top">
			<div align="center" style="padding: 20px 0px;" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 28px; color: #000000;line-height: 34px;"><span style="font-family: Canaro, sans-serif; font-size: 28px; color: #000000;line-height: 34px;"><strong>Booking <span style="color:#FF0E0E">canceled</span>.<br />
			Your refund is on the way. </strong> </span></span></div>
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
						<td style="font-family: Arial, sans-serif; font-weight: bold; font-size: 16px; color: #000000; line-height: 20px; letter-spacing: 3px;">{{$orders->booking_id}}</td>
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
						<table border="0" cellpadding="0" cellspacing="0" style="background:#FF0E0E; border-radius: 20px; width: 100px;">
							<tbody>
								<tr>
									<td align="center" style="padding: 2px 4px 2px 4px;"><img alt="" height="18" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/cancel.png" style="display: block;" width="18" /></td>
									<td style="font-family: Arial, sans-serif; font-size: 12px; color: #ffffff; font-weight: bold; text-decoration: none;">Cancelled</td>
								</tr>
							</tbody>
						</table>
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
			<td align="center" valign="top"><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;"><span style="font-weight: bold; text-decoration: none;">{{ $orders->user->name }}</span> <span style="font-weight: normal; text-decoration: none;">, since we couldn't confirm the adventure within 72 hours of booking, we've had to cancel it. Your refund has been processed and sent to your original payment method. </span> </span></td>
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
			<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Your payment was successfully processed.</span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:10px auto auto auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td style="width: 35px;"><img alt="" border="0" height="24" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/red.png" style="display: block;" width="24" /></td>
			<td><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">Final booking confirmation</span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 0 auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Tour operator and/or airline carriers are confirming your trip</span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:10px auto auto auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td style="width: 35px;"><img alt="" border="0" height="24" src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/refund.png" style="display: block;" width="24" /></td>
			<td><span style="font-family: Canaro, sans-serif; font-size: 16px; color: #000000;">Refund</span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin: 0 auto;width: 100%;text-align: left;">
	<tbody>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">A full refund has been processed and sent to your original payment method.</span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate;height: 15px; line-height: 15px; font-size: 13px;width:100%;margin-top:20px;margin-bottom:20px">
	<tbody>
		<tr>
			<td align="center" style="border-radius: 3px;" valign="middle">
			<div style="overflow: hidden; border-radius: 3px;"><a href="{{ rtrim(config('frontend.url'), '/') }}/my-trips" target="_blank" style="background-color:#ff6c0e;font-size:14px;font-weight:bold;line-height:37px;width:169px;color:#ffffff;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;" >View booking</a>

			<div style="height: 10px; line-height: 10px; font-size: 8px;">&nbsp;</div>

			<div>
			<div style="line-height: 17px;"><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;"><span style="text-decoration: none;">If you need help, </span><span style="color: #82cf45; font-weight: bold; text-decoration: none;">contact us</span></span></div>
			</div>
			</div>
			</td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="padding: 20px 30px; border-radius: 20px; border-width: 1px; border-color: #82cf45; border-style: dotted;border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
	<tbody>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Frequently Asked Questions</span></td>
		</tr>
		<tr height="20" style="height: 20px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">Why was my trip canceled?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">To offer you the best selection and prices for adventure trips, we gather data from multiple tour operators. By default, only tours with guaranteed departures and automatic confirmation appear in search results. However, unforeseen circumstances—such as sudden cancellations due to weather or other significant reasons—can sometimes lead operators to decline a booking. </span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">When will the refund appear in my bank account?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px;">Normally, the refund appears in your account within 1-3 business days. However, due to international banking regulations, it may take up to 10 business days depending on your country and bank. If you haven’t received it by then, please contact your bank. </span></td>
		</tr>
		<tr height="15" style="height: 15px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;font-weight: bold;">Does it mean all my booking requests will be canceled?</span></td>
		</tr>
		<tr height="10" style="height: 10px;">
		</tr>
		<tr>
			<td><span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;">No, this doesn’t mean all your booking requests will be canceled. Most are confirmed automatically, but in rare cases, unforeseen issues may cause a cancellation. These situations are uncommon, and we still encourage you to book another trip once you receive your refund. </span></td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate;height: 15px; line-height: 15px; font-size: 13px;width:100%;margin-top:20px;">
	<tbody>
		<tr>
			<td align="center" style="border-radius: 3px;" valign="middle">
			<div style="overflow: hidden; border-radius: 3px;"><a href="{{ rtrim(config('frontend.url'), '/') }}/contact" target="_blank" style="background-color:#82CF45;font-size:14px;font-weight:bold;line-height:37px;width:169px;color:#ffffff;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;box-sizing:border-box;">Book another trip</a></div>
			</td>
		</tr>
	</tbody>
</table>

<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;margin-top:25px">
	<tbody>
		<tr>
			<td align="left" valign="middle"><span style="font-family: Canaro, sans-serif; font-size: 25px; color: #000000;">Refund</span></td>
		</tr>
	</tbody>
</table>

<div border="0" cellpadding="0" cellspacing="0" style="border-radius: 20px; border-width: 1px; border-color: #82cf45;border-style: solid; border-collapse: separate;width:100%;max-width:600px;margin: 25px auto auto;">
<div style="padding: 20px 30px;">
<table border="0" style="width:100%;margin-top:10px">
	<tbody>
		<tr style="margin-bottom:10px" valign="middle">
			<td><a href="#" style="font-size:14px;line-height: 18px;widtr: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Date and time</a> <span>{{ \Carbon\Carbon::parse($orders->stripe_created)->format('M d, Y') }}</span></td>
		</tr>
		<tr style="margin-bottom:10px" valign="middle">
			<td><a href="#" style="font-size:14px;line-height: 18px;widtr: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Payment method</a> <span>@if ($orders->last_4)<strong>Visa</strong>****{{$orders->last_4}}@else<strong>{{$orders->payment_method}}</strong>@endif</span></td>
		</tr>
		<tr style="margin-bottom:10px" valign="middle">
			<td><a href="#" style="font-size:14px;line-height: 18px;widtr: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Refund Id</a> <span>{{$orders->last_charge}}</span></td>
		</tr>
		<tr style="margin-bottom:20px" valign="middle">
			<td><a href="#" style="font-size:14px;line-height: 18px;widtr: fit-content;border: 2px dotted #82cf45;border-radius:10px;display:inline-block;font-family:Canaro, sans-serif;text-align:center;text-decoration:none;padding: 2px 10px;color: #4f4f4f;" target="_blank">Amount</a> <span>${{ number_format( ceil($orders->paid), 2 ) }} USD</span></td>
		</tr>
	</tbody>
</table>
</div>
</div>

<div class="footer" style="margin-top:25px;">
<table style="max-width: 600px;margin:0 auto;width: 100%;">
	<tbody>
		<tr>
			<td><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i980061126.png" style="vertical-align: middle;width: 150px;" /></td>
			<td><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1150146922.png" style="vertical-align: middle;width: 150px;" /></td>
			<td style="text-align: right;">
			<table style="width: 100%;">
				<tbody>
					<tr>
						<td><a href="https://www.facebook.com/VibeAdventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/facebook.png" style="width:32px" /></a></td>
						<td><a href="https://www.instagram.com/vibe.adventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/instagram.png" style="width:32px" /></a></td>
						<td><a href="https://www.youtube.com/channel/UCQ9qyA-fVkdXarHBlzDCUBA?view_as=subscriber"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/youtube.png" style="width:32px" /></a></td>
					</tr>
					<tr>
						<td><a href="https://www.tiktok.com/@vibeadventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/tiktok.png" style="width:32px" /></a></td>
						<td><a href="https://www.pinterest.com/vibe_adventures/"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/pinterest.png" style="width:32px" /></a></td>
						<td><a href="https://twitter.com/vibe_adventures"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/twitter.png" style="width:32px" /></a></td>
					</tr>
				</tbody>
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
