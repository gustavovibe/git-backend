<html>

<head>
	<title>Booking Confirmation</title>
</head>

<body>
	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" style="max-width: 203px;" valign="middle">
                    <a href="https://hopeful-nobel.74-208-189-166.plesk.page/" target="_blank">
                        <img alt="" border="0" class="w203px"
						src="https://vibeadventures.be/images//logo.png"
						style="display: block; max-width: 203px; width: 100%;" width="203" />
                    </a>
                </td>
				<td style="width:20px">&nbsp;</td>
				<td><span
					style="font-family: Canaro, sans-serif;font-size: 12px;color: #000000;padding-top: 5px;display: block;">If
					you need help, <a style="color: #82cf45; font-weight: bold;" href="https://hopeful-nobel.74-208-189-166.plesk.page/contact" target="_blank">contact us</a></span></td>
			</tr>
		</tbody>
	</table>

	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" bgcolor="#ffffff" style="padding: 0px;" valign="top">
					<div style="padding: 20px 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 28px; color: #000000;line-height: 34px;">
								Vibe Adventures <strong style="color: #82cf45;">got your enquiry</strong>
                        </span>
                    </div>
				</td>
			</tr>
		</tbody>
	</table>

	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" bgcolor="#ffffff" style="padding: 0px;" valign="top">
					<div style="padding: 0px 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;line-height: 34px;">
								Thank you for your interest in booking with us!
                        </span>
                    </div>
				</td>
			</tr>
            <tr>
				<td align="left" bgcolor="#ffffff" style="padding: 0px;" valign="top">
					<div style="padding: 0px 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;line-height: 34px;">
                            We hope you enjoy the experience. We are currently in the <b>testing phase</b> and will be launching publicly soon. Once ready, we will send you the details via email so you can continue with your booking.
                        </span>
                    </div>
				</td>
			</tr>
            <tr>
				<td align="left" bgcolor="#ffffff" style="padding: 0px;" valign="top">
					<div style="padding: 0px 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;line-height: 34px;">
                            In the meantime, if you’d like to share your experience using our platform, <a style="color: #82cf45;" href="https://tidycal.com/vibe-adventures/a-call-with-vibe-adventures-m5vzwr8"><b>schedule a call with us</b></a> and receive a <b>$100 USD discount</b> on your future booking!
                        </span>
                    </div>
				</td>
			</tr>
            <tr>
				<td align="left" bgcolor="#ffffff" style="padding: 0px;" valign="top">
					<div style="padding: 20px 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #000000;line-height: 34px;">
                                Thank you again for your support!
                        </span>
                    </div>
				</td>
			</tr>
		</tbody>
	</table>


	<table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%;">
		<tbody>
			<tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Name</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								{{$data['name']}} {{$data['last_name'] ?? null}}
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Email</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								{{ $data['email'] }}
                        </span>
                    </div>
				</td>
            </tr>
            @if ($data['booking_id'])
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Bookin #</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								{{ $data['booking_id'] }}
                        </span>
                    </div>
				</td>
            </tr>
            @endif
            @if ($data['adventure_link'] || $data['tour_details'])
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Adventure @if ($data['adventure_link']) Link @endif</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
							@if ($data['adventure_link'])
                                <a href="{{ $data['adventure_link'] }}" target="_blank">
                                    Adventure
                                </a>
                            @else
                                <a href="{{ $data['tour_details']['current_url'] }}" target="_blank" style="color: #82cf45;">
                                    {{ $data['tour_details']['tour_name'] }}
                                </a> &nbsp;
                                ({{ $data['tour_details']['tour_length_days'] }} days)
                            @endif
                        </span>
                    </div>
				</td>
            </tr>
            @endif
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Departure Date</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
							{{ $data['departure_date'] }}
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
								<strong>Travelers</strong>
                        </span>
                    </div>
				</td>
            </tr>
            <tr>
				<td align="left" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 0px; padding-top: 0px;" valign="top">
					<div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 14px; color: #505050;line-height: 34px;">
							{{ $data['travelers'] }} person(s)
                        </span>
                    </div>
				</td>
            </tr>
            </tbody>
        </table>

    @if ($data['tour_details'])
    <table border="0" cellpadding="0" cellspacing="0" style="max-width: 600px;margin:0 auto;width: 100%; padding-top:10px; padding-bottom:10px;">
        <tbody>
            <tr>
                <td align="center" bgcolor="#FFFFFF" style="padding: 30px; padding-bottom: 5px; padding-top: 5px; border:1px solid #a4a4a4; border-radius: 10px 10px 0 0;" valign="top">
                    <div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 24px; color: #505050;line-height: 34px;">
                            <a href="{{ $data['tour_details']['current_url'] }}" target="_blank" style="color: #82cf45;">
                                {{ $data['tour_details']['tour_name'] }}
                            </a>
                        </span>
                        <br>
                        <span style="font-family: Canaro, sans-serif; font-size: 12px; color: #505050;line-height: 34px;">
                            <strong>Duration: </strong>{{ $data['tour_details']['tour_length_days'] }} days
                        </span>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <span style="font-family: Canaro, sans-serif; font-size: 12px; color: #505050;line-height: 34px;">
                            <strong style="color: #82cf45;">
                                {{ $data['tour_details']['ratings'] }}
                            </strong> ({{ $data['tour_details']['reviews_count'] }} reviews)
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center" bgcolor="#e6f5da" style="padding: 30px; padding-bottom: 5px; padding-top: 5px; border:1px solid #a4a4a4; border-radius: 0 0 10px 10px;" valign="top">
                    <div style="padding: 20px 0px; padding-bottom: 0px; padding-top: 0px;" valign="middle">
                        <span style="font-family: Canaro, sans-serif; font-size: 16px; color: #505050;line-height: 34px;">
                            <strong>Starts in: </strong>{{ $data['tour_details']['start_city'] }}
                        </span>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span style="font-family: Canaro, sans-serif; font-size: 16px; color: #505050;line-height: 34px;">
                            <strong>
                                Ends in: 
                            </strong> {{ $data['tour_details']['end_city'] }}
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    @endif

	<div class="footer" style="margin-top:25px;">
		<table style="max-width: 600px;margin:0 auto;width: 100%;">
			<tbody>
				<tr>
					<td><a href="https://www.trustindex.io/reviews/vibeadventures.com" target="_blank"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i980061126.png"
							style="vertical-align: middle;width: 150px;" /></a></td>
					<td><a href="https://www.trustindex.io/reviews/vibeadventures.com" target="_blank"><img src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/i-1150146922.png"
							style="vertical-align: middle;width: 150px;" /></a></td>
					<td style="text-align: right;">
						<table style="width: 100%;">
							<tr>
								<td><a href="https://www.facebook.com/VibeAdventures" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/facebook.png"
											style="width:32px" /></a></td>
								<td><a href="https://www.instagram.com/vibe.adventures" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/instagram.png"
											style="width:32px" /></a></td>
								<td><a
										href="https://www.youtube.com/channel/UCQ9qyA-fVkdXarHBlzDCUBA?view_as=subscriber" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/youtube.png"
											style="width:32px" /></a></td>
							</tr>
							<tr>
								<td><a href="https://www.tiktok.com/@vibeadventures" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/tiktok.png"
											style="width:32px" /></a></td>
								<td><a href="https://www.pinterest.com/vibe_adventures/" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/pinterest.png"
											style="width:32px" /></a></td>
								<td><a href="https://twitter.com/vibe_adventures" target="_blank"><img
											src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/twitter.png"
											style="width:32px" /></a></td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>

		<table style="max-width: 600px;margin:0 auto;width: 100%;">
			<tbody>
				<tr>
					<td><span style="font-family: Canaro, sans-serif; font-size: 12px; color: #4f4f4f;">Ayuntamiento
							115, Colonia Centro<br />
							<br />
							Cuauhtémoc, CDMX 06000 </span></td>
				</tr>
			</tbody>
		</table>
	</div>
</body>

</html>