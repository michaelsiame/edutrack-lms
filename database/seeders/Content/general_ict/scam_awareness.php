<?php

return [
    'title' => 'Cybersecurity: Scam Awareness',
    'description' => 'Recognise and avoid the online and mobile-money scams common in Zambia: fake winnings, job scams, phishing links, SIM-swap and social engineering — and know how to verify, protect yourself and report fraud.',
    'lessons' => [
        [
            'title' => 'How Scams Work and Common Zambian Scams',
            'content' => <<<'HTML'
<h3>What is a scam?</h3>
<p>A <strong>scam</strong> is a trick used to steal your money or personal information. Scammers do not usually "hack" complicated systems — instead they trick <em>people</em>. This is called <strong>social engineering</strong>: they use fear, excitement or urgency to make you act before you think.</p>
<h3>Common scams in Zambia</h3>
<ul>
<li><strong>"You have won!"</strong> — an SMS or call says you won a promotion, lottery or airtime from a network or bank. To "claim" it you must send money or share a PIN. Real promotions never ask you to pay to receive a prize.</li>
<li><strong>Mobile-money "wrong number" trick</strong> — someone claims they sent money to your number by mistake and begs you to send it back. The "deposit" message is fake; if you send, you lose real money.</li>
<li><strong>Fake agent / "reverse the transaction"</strong> — a caller pretends to be from Airtel/MTN/Zamtel or a bank and says there is a problem; they ask for your PIN or a code to "fix" it.</li>
<li><strong>Job and scholarship scams</strong> — you are offered a job or college place but must first pay a "registration" or "processing" fee.</li>
<li><strong>Phishing links</strong> — a message with a link to a fake page that looks like your bank or Facebook, asking you to "log in" so they can steal your password.</li>
<li><strong>Romance / friendship scams</strong> — someone you met online builds trust, then asks for money for an "emergency".</li>
</ul>
<p><strong>Try this:</strong> Which of these have you or someone you know received? Note down the one you think is most common in your area.</p>
HTML,
        ],
        [
            'title' => 'Red Flags: How to Spot a Scam Message',
            'content' => <<<'HTML'
<h3>Learn the warning signs</h3>
<p>Almost every scam shows one or more of these <strong>red flags</strong>. If you see them, stop and check.</p>
<ul>
<li><strong>Urgency:</strong> "Act now!", "Your line will be blocked today!" — pressure to stop you thinking.</li>
<li><strong>Too good to be true:</strong> a prize, job or deal you never applied for.</li>
<li><strong>Asks for a PIN, password or OTP:</strong> no genuine bank, network or company will <em>ever</em> ask for these.</li>
<li><strong>Asks you to pay to receive money or a prize:</strong> real winnings never require an upfront fee.</li>
<li><strong>Strange sender or link:</strong> a normal-looking name but an odd number, or a link that is misspelled (e.g. <em>airtel-promo-claim.xyz</em>).</li>
<li><strong>Poor spelling and grammar</strong>, or a generic greeting like "Dear Customer".</li>
</ul>
<h3>The golden rules</h3>
<ul>
<li><strong>Never share your PIN or OTP</strong> with anyone, for any reason.</li>
<li><strong>Slow down.</strong> Scammers rely on speed. A genuine matter can wait five minutes while you check.</li>
<li><strong>Verify independently.</strong> Do not use the number or link in the message. Call the official number on the back of your card, the company's real shop, or a number you already trust.</li>
</ul>
<p><strong>Try this:</strong> A message reads: "CONGRATS! Your number won K5,000 in the MTN draw. Send K50 to 097xxxxxxx to claim. Reply NOW or lose it." List three red flags you can see.</p>
HTML,
        ],
        [
            'title' => 'Protecting Yourself and Reporting Fraud',
            'content' => <<<'HTML'
<h3>Protect yourself every day</h3>
<ul>
<li>Keep your <strong>PIN and OTP secret</strong> — never type them because someone on a call told you to.</li>
<li>Use a <strong>strong password</strong> and a screen lock on your phone.</li>
<li>Do not click links in messages you did not expect; open the app or type the website yourself.</li>
<li>Confirm mobile-money deposits by checking your <strong>actual balance</strong>, not just the SMS — fake SMS messages are easy to make.</li>
<li>Be careful what personal details you share online; scammers use them to sound convincing.</li>
</ul>
<h3>If you are targeted</h3>
<ol>
<li><strong>Stop.</strong> Do not send money or share any code.</li>
<li><strong>Verify</strong> using an official channel you trust.</li>
<li><strong>Block</strong> the number and keep screenshots as evidence.</li>
</ol>
<h3>If you have already been scammed</h3>
<p>Act fast — speed can save your money:</p>
<ul>
<li>Call your mobile-money provider or bank <strong>immediately</strong> and ask them to freeze or reverse the transaction.</li>
<li>Report to the <strong>Zambia Police</strong> (and keep the report number).</li>
<li>Report the number to your network operator.</li>
<li>Warn family and friends so they are not caught by the same trick.</li>
</ul>
<p>Being scammed is not shameful — scammers are skilled tricksters. Reporting quickly helps you and protects others.</p>
<p><strong>Try this:</strong> Write the three steps you would take in the first five minutes after realising money was taken from your mobile-money account.</p>
HTML,
        ],
    ],
    'quiz' => [
        'title' => 'Quiz: Scam Awareness',
        'description' => 'Test whether you can spot and respond to common scams.',
        'questions' => [
            [
                'type' => 'Multiple Choice',
                'text' => 'A caller says he is from your mobile-money provider and asks for your PIN to "fix a problem". You should:',
                'explanation' => 'No genuine provider ever asks for your PIN or OTP. Refuse and hang up.',
                'options' => [
                    ['text' => 'Refuse — never share your PIN, and hang up', 'correct' => true],
                    ['text' => 'Give the PIN so they can help quickly', 'correct' => false],
                    ['text' => 'Send a small amount to test', 'correct' => false],
                    ['text' => 'Share your OTP instead of the PIN', 'correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which of these is a classic scam red flag?',
                'explanation' => 'Urgency and pressure are designed to stop you thinking clearly.',
                'options' => [
                    ['text' => 'Pressure to "act now or lose it"', 'correct' => true],
                    ['text' => 'A message you can verify calmly', 'correct' => false],
                    ['text' => 'An official number on your bank card', 'correct' => false],
                    ['text' => 'Checking your real balance', 'correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'You "won" a promotion you never entered, but must send K50 to claim it. This is:',
                'explanation' => 'Real prizes never require an upfront payment — this is a scam.',
                'options' => [
                    ['text' => 'A scam — real prizes never require a fee', 'correct' => true],
                    ['text' => 'A genuine reward', 'correct' => false],
                    ['text' => 'Normal for all promotions', 'correct' => false],
                    ['text' => 'Safe if the amount is small', 'correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'The safest way to confirm a mobile-money deposit really arrived is to:',
                'explanation' => 'Fake SMS messages are easy to create; always check your actual balance.',
                'options' => [
                    ['text' => 'Check your actual account balance', 'correct' => true],
                    ['text' => 'Trust the SMS message you received', 'correct' => false],
                    ['text' => 'Ask the sender if it is real', 'correct' => false],
                    ['text' => 'Assume it is fine', 'correct' => false],
                ],
            ],
            [
                'type' => 'Short Answer',
                'text' => 'You realise money was just stolen from your mobile-money account. What is the FIRST thing you should do?',
                'explanation' => 'Contact your provider/bank immediately to freeze or reverse the transaction — speed matters most.',
                'correct_answer' => 'Immediately call your mobile-money provider or bank to freeze/reverse the transaction.',
            ],
        ],
    ],
    'assignment' => [
        'title' => 'Spot the Scam Worksheet',
        'description' => 'Apply what you learned to real-looking messages.',
        'instructions' => 'Find or write down three suspicious messages (SMS, WhatsApp, email, or examples from this lesson). For each one: (1) identify the type of scam, (2) list the red flags you can see, and (3) describe exactly what you would do. Then write a short paragraph teaching a family member how to avoid mobile-money scams. Submit as a PDF or Word document.',
    ],
];
