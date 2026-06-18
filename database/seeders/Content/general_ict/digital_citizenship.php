<?php

return [
    'title' => 'Being a Good Digital Citizen',
    'description' => 'Use the internet safely, respectfully and responsibly: your online identity, good manners online, protecting your privacy, and telling real information from fake.',
    'lessons' => [
        [
            'title' => 'Your Online Identity and Reputation',
            'content' => <<<'HTML'
<h3>What is digital citizenship?</h3>
<p>A <strong>digital citizen</strong> is someone who uses computers, phones and the internet in a safe, respectful and responsible way. Just like being a good citizen of Zambia means following the law and treating people well, being a good digital citizen means behaving well online.</p>
<h3>Your digital footprint</h3>
<p>Everything you do online — every photo you post, every comment you make on Facebook, every message on WhatsApp — leaves a trail called your <strong>digital footprint</strong>. This footprint can last for years, and other people can see it: friends, family, and even future employers.</p>
<ul>
<li>Employers often search for your name before offering a job.</li>
<li>A rude comment or an embarrassing photo can stay online long after you forget it.</li>
<li>What you post becomes part of how people see you.</li>
</ul>
<h3>Build a positive footprint</h3>
<p>Think before you post. Ask yourself: "Would I be happy for my parents, my pastor, or my future boss to see this?" If the answer is no, do not post it. Share things that show your skills, your achievements and your good character.</p>
<p><strong>Try this:</strong> Search your own name on Google and Facebook. What comes up? Decide one thing you could clean up and one positive thing you could add (for example, a post about completing this course).</p>
HTML,
        ],
        [
            'title' => 'Netiquette: Good Manners Online',
            'content' => <<<'HTML'
<h3>Treat people the way you would in person</h3>
<p><strong>Netiquette</strong> means "internet etiquette" — the good manners we use when communicating online. The golden rule is simple: there is a real person on the other side of the screen, so treat them the way you would if you were standing in front of them at the market or in church.</p>
<h3>Simple rules of netiquette</h3>
<ul>
<li><strong>Be polite.</strong> Say please and thank you, even in a quick WhatsApp message.</li>
<li><strong>Do not SHOUT.</strong> Typing in all capital letters feels like shouting.</li>
<li><strong>Do not spread gossip or insults.</strong> Forwarding a hurtful message makes you part of the problem.</li>
<li><strong>Respect people's time.</strong> Keep group messages relevant; avoid endless "good morning" forwards in a work group.</li>
<li><strong>Check before you forward.</strong> Do not share news, photos or voice notes unless you know they are true.</li>
</ul>
<h3>Cyberbullying</h3>
<p>Using the internet to insult, threaten or embarrass someone is <strong>cyberbullying</strong>, and it is wrong. If someone bullies you online, do not reply in anger. Save the evidence (screenshots), block the person, and tell a trusted adult or report it.</p>
<p><strong>Try this:</strong> Rewrite this rude message politely: "WHY HAVENT U SENT THE MONEY?? ALWAYS LATE!!"</p>
HTML,
        ],
        [
            'title' => 'Privacy, Passwords and Spotting Fake Information',
            'content' => <<<'HTML'
<h3>Protect your privacy</h3>
<p>Your personal information — your NRC number, your phone number, your home address, your photos — is valuable. Once you give it away online, you cannot take it back. Share it only with people and services you trust.</p>
<ul>
<li>Check your Facebook and WhatsApp privacy settings so strangers cannot see everything.</li>
<li>Never share your NRC, bank or mobile-money details in a chat.</li>
<li>Be careful what you post about other people, too.</li>
</ul>
<h3>Strong passwords</h3>
<p>A password is the key to your digital life. A weak password is like leaving your door open.</p>
<ul>
<li>Use at least 8 characters with letters, numbers and a symbol — for example <strong>Kalomo#2026</strong>.</li>
<li>Do not use your name, birthday or "1234".</li>
<li>Use a different password for important accounts (email, mobile money).</li>
<li>Never share your password or your one-time PIN (OTP) with anyone.</li>
</ul>
<h3>Telling real information from fake</h3>
<p>Not everything online is true. Before you believe or share something, check it:</p>
<ul>
<li><strong>Who said it?</strong> Is it a known, trusted source (e.g. a real news organisation)?</li>
<li><strong>Is it on other trusted sites?</strong> If only one strange page reports it, be suspicious.</li>
<li><strong>Does it sound too shocking or too good?</strong> Fake news is designed to make you react quickly.</li>
</ul>
<p><strong>Try this:</strong> A WhatsApp message says "Forward to 10 people or your line will be disconnected today." Explain in one sentence why this is fake.</p>
HTML,
        ],
    ],
    'quiz' => [
        'title' => 'Quiz: Being a Good Digital Citizen',
        'description' => 'Check your understanding of online identity, netiquette and privacy.',
        'questions' => [
            [
                'type' => 'Multiple Choice',
                'text' => 'What is a "digital footprint"?',
                'explanation' => 'Your digital footprint is the lasting trail of everything you do online.',
                'options' => [
                    ['text' => 'The trail of everything you do and post online', 'correct' => true],
                    ['text' => 'A type of computer virus', 'correct' => false],
                    ['text' => 'The size of your phone screen', 'correct' => false],
                    ['text' => 'A footprint-shaped app icon', 'correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Typing a message in ALL CAPITAL LETTERS online is considered:',
                'explanation' => 'In netiquette, all-caps reads as shouting and feels aggressive.',
                'options' => [
                    ['text' => 'Shouting / rude', 'correct' => true],
                    ['text' => 'Polite and clear', 'correct' => false],
                    ['text' => 'Required for emails', 'correct' => false],
                    ['text' => 'A way to save data', 'correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Which of these is the strongest password?',
                'explanation' => 'A mix of upper/lowercase letters, numbers and a symbol is strongest; names and 1234 are weak.',
                'options' => [
                    ['text' => 'Kalomo#2026', 'correct' => true],
                    ['text' => '1234', 'correct' => false],
                    ['text' => 'password', 'correct' => false],
                    ['text' => 'your first name', 'correct' => false],
                ],
            ],
            [
                'type' => 'Multiple Choice',
                'text' => 'Before sharing shocking news you saw online, you should first:',
                'explanation' => 'Always verify the source and check trusted outlets before sharing.',
                'options' => [
                    ['text' => 'Check whether a trusted source reported it', 'correct' => true],
                    ['text' => 'Forward it immediately to everyone', 'correct' => false],
                    ['text' => 'Add your own exaggeration', 'correct' => false],
                    ['text' => 'Believe it because it is shocking', 'correct' => false],
                ],
            ],
            [
                'type' => 'Short Answer',
                'text' => 'Give one reason you should never share your mobile-money PIN or one-time PIN (OTP) with anyone.',
                'explanation' => 'The PIN/OTP is the key to your money; anyone who has it can steal your funds. No genuine company will ever ask for it.',
                'correct_answer' => 'Anyone with your PIN/OTP can access and steal your money; genuine services never ask for it.',
            ],
        ],
    ],
    'assignment' => [
        'title' => 'Clean Up and Build Your Online Reputation',
        'description' => 'A short practical task to manage your own digital footprint.',
        'instructions' => 'Search your own name on Google and Facebook. In a one-page document: (1) describe what currently shows up, (2) list two things you will remove or make private, (3) write one positive post you will publish (for example about completing this course), and (4) list three netiquette rules you will follow in your work WhatsApp groups. Submit as a PDF or Word document.',
    ],
];
