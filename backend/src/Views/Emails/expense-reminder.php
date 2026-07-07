<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0"
                   style="background:#ffffff;border-radius:10px;overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="background:#2563eb;padding:24px;color:#ffffff;">
                        <h1 style="margin:0;font-size:24px;">
                            Expense Reminder
                        </h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:32px;">

                        <p style="font-size:16px;color:#333;margin-top:0;">
                            Hello,
                        </p>

                        <p style="color:#555;line-height:1.6;">
                            This is a friendly reminder that you have the following expense due today.
                        </p>

                        <table width="100%" cellpadding="10" cellspacing="0"
                               style="margin-top:25px;border-collapse:collapse;border:1px solid #e5e7eb;">

                            <tr style="background:#f9fafb;">
                                <th align="left">Expense</th>
                                <th align="left">Amount</th>
                                <th align="left">Due Date</th>
                            </tr>

                            <tr>
                                <td><?= htmlspecialchars($expense['name']) ?></td>
                                <td>$<?= number_format($expense['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($expense['date']) ?></td>
                            </tr>

                        </table>

                        <p style="margin-top:30px;color:#555;line-height:1.6;">
                            Staying on top of your expenses helps you avoid missed payments and keeps your finances organized.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:20px;background:#f9fafb;text-align:center;color:#888;font-size:12px;">
                        This is an automated reminder.<br>
                        Please do not reply to this email.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>