<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Pyroll Slip</title>

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ public_path("logo.png") }}"
                                    style="width: 100%; max-width: 100px" />
                            </td>

                            <td>
                                Slip Gaji #: {{ date('YmdHis') }}<br />
                                Created: {{ $data->created_at }}<br />
                                Due: {{ date('Y-m-d') }}
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
                                PT. Andy Jaya, Acc.<br />
                                Jl.Citra raya utama timur ciakar, Panongan,<br />
                                Tangerang regency, banten.
                            </td>

                            <td>
                                {{ $user->name }}<br />
                                {{ $user->email }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Method</td>
                <td>Check #</td>
            </tr>
            <tr class="details">
                <td>Schedule Total</td>
                <td>{{ $data->total_schedule }}</td>
            </tr>
            <tr class="details">
                <td>Present Total</td>
                <td>{{ $data->total_present }}</td>
            </tr>
            <tr class="details">
                <td>Late Present Total</td>
                <td>{{ $data->total_late }}</td>
            </tr>
            <tr class="details">
                <td>Unlate Present Total</td>
                <td>{{ $data->total_unlate }}</td>
            </tr>
            <tr class="details">
                <td>Early Present Total</td>
                <td>{{ $data->total_early }}</td>
            </tr>

            <tr class="heading">
                <td>Composition</td>
                <td>Price</td>
            </tr>
            @php
                function format_rupiah($number) {
                    $formatted_number = number_format($number, 2, ',', '.');
                    $formatted_number = 'Rp ' . $formatted_number;
                    return $formatted_number;
                }
            @endphp 
            <tr class="item">
                <td>Subtotal</td>
                <td>{{ format_rupiah($data->subtotal_payroll) }}</td>
            </tr>
            @foreach ($component as $k => $v)
            <tr class="item">
                <td>{{ $v->title }}</td>
                <td>{{ format_rupiah($v->amount) }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td></td>
                <td>Total: {{ format_rupiah($data->total_payroll) }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
