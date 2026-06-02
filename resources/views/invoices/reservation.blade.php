<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $reservation->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; line-height: 24px; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; font-weight: bold; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
        .badge { padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .badge-paid { background: #d4edda; color: #155724; }
        .badge-unpaid { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">EME'S Apartelle</td>
                            <td>
                                Invoice #: {{ $reservation->id }}<br>
                                Created: {{ $date }}<br>
                                Status: <span class="badge {{ $reservation->payment_status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">
                                    {{ $reservation->payment_status }}
                                </span>
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
                                EME's Apartelle<br>
                                123 Sunshine Street<br>
                                City, Province, 4000
                            </td>
                            <td>
                                {{ $reservation->user->name }}<br>
                                {{ $reservation->user->email }}<br>
                                {{ $reservation->user->contact_number ?? '' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Description</td>
                <td>Details</td>
            </tr>
            <tr class="item">
                <td>Room Type</td>
                <td>{{ $reservation->room->room_type }} (#{{ $reservation->room->room_number }})</td>
            </tr>
            @php
                $checkIn = \Carbon\Carbon::parse($reservation->check_in);
                $checkOut = \Carbon\Carbon::parse($reservation->check_out);
                $nights = $checkIn->diffInDays($checkOut);
                if ($nights <= 0) $nights = 1;
            @endphp
            <tr class="item">
                <td>Stay Duration</td>
                <td>{{ $reservation->check_in }} to {{ $reservation->check_out }} ({{ $nights }} night{{ $nights > 1 ? 's' : '' }})</td>
            </tr>
            @if(in_array($reservation->room->room_type, ['Family Room', 'Barkadahan Room']))
            <tr class="item">
                <td>Guests</td>
                <td>
                    {{ $reservation->guests ?? 1 }} Guests
                    <br>
                    <span style="color: #666; font-size: 13px; font-style: italic;">
                        (Room Minimum Capacity: {{ $reservation->room->min_occupancy }} Guests)
                    </span>
                </td>
            </tr>
            <tr class="item last">
                <td>Per Head Rate</td>
                <td>
                    ₱{{ number_format($reservation->room->price_per_head, 2) }} / head / night
                    <br>
                    <span style="color: #666; font-size: 13px; font-style: italic;">
                        ((₱{{ number_format($reservation->room->price_per_head, 2) }} × {{ $reservation->guests ?? 1 }} guests) × {{ $nights }} night{{ $nights > 1 ? 's' : '' }})
                    </span>
                </td>
            </tr>
            @else
            <tr class="item last">
                <td>Nightly Rate</td>
                <td>
                    ₱{{ number_format($reservation->room->price_per_night, 2) }} / night
                    <br>
                    <span style="color: #666; font-size: 13px; font-style: italic;">
                        (₱{{ number_format($reservation->room->price_per_night, 2) }} × {{ $nights }} night{{ $nights > 1 ? 's' : '' }})
                    </span>
                </td>
            </tr>
            @endif
            <tr class="total">
                <td></td>
                <td>Total: ₱{{ number_format($reservation->total_amount, 2) }}</td>
            </tr>
        </table>
        <div style="margin-top: 50px; text-align: center; color: #999; font-size: 12px;">
            Thank you for your business!
        </div>
    </div>
    <script>
        if (window.location.search.includes('print=true')) {
            window.onload = function() {
                window.print();
            }
        }
    </script>
</body>
</html>
