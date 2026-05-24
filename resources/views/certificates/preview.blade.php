<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificate - {{ $certificate_number ?? 'Preview' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Great+Vibes&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
@page { size: A4 portrait; margin: 0; }
@media print {
    body { margin: 0; padding: 0; background: white; }
    .certificate-page { box-shadow: none !important; margin: 0 !important; }
}
.font-script { font-family: 'Great Vibes', cursive; }
.font-serif { font-family: 'Playfair Display', Georgia, serif; }
.corner-triangle-tl {
    width: 0; height: 0;
    border-top: 40px solid #F26522;
    border-right: 40px solid transparent;
}
.corner-triangle-tr {
    width: 0; height: 0;
    border-top: 40px solid #F26522;
    border-left: 40px solid transparent;
}
.corner-triangle-bl {
    width: 0; height: 0;
    border-bottom: 40px solid #F26522;
    border-right: 40px solid transparent;
}
.corner-triangle-br {
    width: 0; height: 0;
    border-bottom: 40px solid #F26522;
    border-left: 40px solid transparent;
}
.gold-line {
    height: 1px;
    background: linear-gradient(to right, transparent, #D4A843, transparent);
}
</style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<!-- Certificate Page -->
<div class="certificate-page bg-white relative" style="width: 210mm; height: 297mm; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">

    <!-- Outer Orange Border -->
    <div class="absolute inset-0 border-[3px] border-[#F26522]"></div>

    <!-- Middle Blue Border -->
    <div class="absolute inset-[6px] border-[2px] border-[#1E3A8A]"></div>

    <!-- Corner Decorations -->
    <div class="corner-triangle-tl absolute top-[6px] left-[6px] z-10"></div>
    <div class="corner-triangle-tr absolute top-[6px] right-[6px] z-10"></div>
    <div class="corner-triangle-bl absolute bottom-[6px] left-[6px] z-10"></div>
    <div class="corner-triangle-br absolute bottom-[6px] right-[6px] z-10"></div>

    <!-- Inner Content -->
    <div class="absolute inset-[14px] flex flex-col">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-6 pt-6 pb-2">
            <!-- Logo -->
            <div class="w-24 flex-shrink-0">
                @if(file_exists(public_path('assets/images/logo.png')))
                <img src="{{ asset('assets/images/logo.png') }}" alt="Edutrack" class="h-20 w-auto">
                @else
                <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-primary-700 font-bold text-sm">EduTrack</span>
                </div>
                @endif
            </div>

            <!-- College Name -->
            <div class="flex-1 text-center px-4">
                <h1 class="font-serif text-[26px] font-bold text-[#1E3A8A] tracking-wider leading-tight">EDUTRACK COMPUTER</h1>
                <h1 class="font-serif text-[26px] font-bold text-[#1E3A8A] tracking-wider leading-tight">TRAINING COLLEGE</h1>
                <div class="flex items-center justify-center gap-1 my-2">
                    <div class="h-px w-16 bg-[#D4A843]"></div>
                    <span class="text-[#D4A843] text-xs">&#9670; &#9670; &#9670;</span>
                    <div class="h-px w-16 bg-[#D4A843]"></div>
                </div>
                <p class="text-[#666666] text-[11px] italic tracking-wide">A skill training college</p>
            </div>

            <!-- TEVETA Logo -->
            <div class="w-24 flex-shrink-0 text-right">
                @if(file_exists(public_path('assets/images/teveta-logo.png')))
                <img src="{{ asset('assets/images/teveta-logo.png') }}" alt="TEVETA" class="h-16 w-auto ml-auto">
                @else
                <div class="w-20 h-16 border border-[#1E3A8A] rounded flex items-center justify-center ml-auto">
                    <span class="text-[#1E3A8A] font-bold text-xs">TEVETA</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Decorative line -->
        <div class="px-8 mb-1">
            <div class="flex items-center">
                <div class="flex-1 h-px bg-[#D4A843]"></div>
                <span class="px-2 text-[#D4A843] text-sm">&#9670;</span>
                <div class="flex-1 h-px bg-[#D4A843]"></div>
            </div>
        </div>

        <!-- CERTIFY LINE -->
        <div class="text-center py-2">
            <div class="flex items-center justify-center gap-3">
                <div class="flex items-center gap-1">
                    <div class="w-1.5 h-1.5 bg-[#D4A843] rotate-45"></div>
                    <div class="w-1.5 h-1.5 bg-[#D4A843] rotate-45"></div>
                </div>
                <h2 class="font-serif text-[13px] font-bold text-[#1E3A8A] tracking-[3px]">THIS IS TO CERTIFY THAT</h2>
                <div class="flex items-center gap-1">
                    <div class="w-1.5 h-1.5 bg-[#D4A843] rotate-45"></div>
                    <div class="w-1.5 h-1.5 bg-[#D4A843] rotate-45"></div>
                </div>
            </div>
        </div>

        <!-- STUDENT NAME -->
        <div class="text-center py-1">
            <span class="font-script text-[52px] text-[#111111] leading-none">{{ $student_name ?? 'Catherine Namakanda' }}</span>
            <div class="flex items-center justify-center mt-1">
                <div class="w-48 h-[2px] bg-[#D4A843]"></div>
            </div>
        </div>

        <!-- BODY TEXT -->
        <div class="text-center py-2">
            <p class="text-[#333333] text-[11px] leading-relaxed">
                having satisfied the requirements for the<br>
                award of the certificate of
            </p>
        </div>

        <!-- COURSE TITLE -->
        <div class="text-center py-1">
            <h3 class="font-serif text-[28px] font-bold text-[#1E3A8A] uppercase tracking-[1px]">{{ strtoupper($course_title ?? 'GENERAL BASIC COMPUTING') }}</h3>
        </div>

        <!-- CLASSIFICATION -->
        @if(($classification ?? 'Pass') !== 'Pass')
        <div class="text-center py-1">
            <span class="font-script text-[32px] text-[#111111]">With {{ $classification ?? 'Merit' }}</span>
            <div class="flex items-center justify-center mt-1 gap-2">
                <div class="w-12 h-px bg-[#D4A843]"></div>
                <span class="text-[#D4A843] text-xs">&#9670;</span>
                <div class="w-12 h-px bg-[#D4A843]"></div>
            </div>
        </div>
        @endif

        <!-- GRADUATION DATE -->
        <div class="text-center py-2">
            <p class="text-[#333333] text-[11px] leading-relaxed">
                was admitted to the certificate at a Graduation<br>
                Ceremony held on the
                <span class="text-[16px] font-bold text-[#1E3A8A] font-serif">&nbsp;{{ $graduation_day ?? '27' }}<sup class="text-[8px]">{{ $graduation_suffix ?? 'th' }}</sup>&nbsp;</span>
                day of
                <span class="text-[16px] font-bold text-[#1E3A8A] italic font-serif">&nbsp;{{ $graduation_month ?? 'March' }}&nbsp;</span><br>
                in the year
                <span class="text-[20px] font-bold text-[#1E3A8A] font-serif">&nbsp;{{ $graduation_year ?? '2026' }}&nbsp;</span>
            </p>
        </div>

        <!-- SIGNATURES + SEAL -->
        <div class="flex items-end justify-between px-8 py-3">
            <!-- Left Signatures -->
            <div class="w-[30%] text-center space-y-6">
                <div>
                    <div class="w-full h-px bg-[#333333] mb-1"></div>
                    <span class="text-[9px] text-[#333333]">Principal</span>
                </div>
                <div>
                    <div class="w-full h-px bg-[#333333] mb-1"></div>
                    <span class="text-[9px] text-[#333333]">Graduate's Signature</span>
                </div>
            </div>

            <!-- Seal -->
            <div class="w-[40%] flex justify-center">
                <svg width="100" height="130" viewBox="0 0 100 130" xmlns="http://www.w3.org/2000/svg">
                    <!-- Outer scalloped ring -->
                    <circle cx="50" cy="48" r="46" fill="none" stroke="#C9A227" stroke-width="2" stroke-dasharray="4,2"/>
                    <!-- Blue circle -->
                    <circle cx="50" cy="48" r="40" fill="#1E3A8A"/>
                    <!-- Inner gold ring -->
                    <circle cx="50" cy="48" r="36" fill="none" stroke="#C9A227" stroke-width="2"/>
                    <!-- Stars on ring -->
                    <text x="50" y="16" text-anchor="middle" fill="#C9A227" font-size="8">&#9733;</text>
                    <text x="50" y="84" text-anchor="middle" fill="#C9A227" font-size="8">&#9733;</text>
                    <text x="18" y="50" text-anchor="middle" fill="#C9A227" font-size="8">&#9733;</text>
                    <text x="82" y="50" text-anchor="middle" fill="#C9A227" font-size="8">&#9733;</text>
                    <!-- Laurel wreath left -->
                    <path d="M22,48 Q30,30 40,28" fill="none" stroke="#C9A227" stroke-width="1.5"/>
                    <path d="M24,52 Q32,35 42,32" fill="none" stroke="#C9A227" stroke-width="1"/>
                    <path d="M22,48 Q30,66 40,68" fill="none" stroke="#C9A227" stroke-width="1.5"/>
                    <path d="M24,44 Q32,61 42,64" fill="none" stroke="#C9A227" stroke-width="1"/>
                    <!-- Laurel wreath right -->
                    <path d="M78,48 Q70,30 60,28" fill="none" stroke="#C9A227" stroke-width="1.5"/>
                    <path d="M76,52 Q68,35 58,32" fill="none" stroke="#C9A227" stroke-width="1"/>
                    <path d="M78,48 Q70,66 60,68" fill="none" stroke="#C9A227" stroke-width="1.5"/>
                    <path d="M76,44 Q68,61 58,64" fill="none" stroke="#C9A227" stroke-width="1"/>
                    <!-- Center star -->
                    <polygon points="50,34 53,44 64,44 55,51 58,62 50,55 42,62 45,51 36,44 47,44" fill="#C9A227"/>
                    <!-- Ribbon top bar -->
                    <rect x="32" y="88" width="36" height="8" rx="2" fill="#F26522"/>
                    <!-- Left ribbon -->
                    <polygon points="32,96 44,96 38,126 24,116" fill="#F26522"/>
                    <!-- Right ribbon -->
                    <polygon points="68,96 56,96 62,126 76,116" fill="#F26522"/>
                    <!-- Ribbon details -->
                    <line x1="38" y1="96" x2="32" y2="122" stroke="#D4520E" stroke-width="1" opacity="0.5"/>
                    <line x1="62" y1="96" x2="68" y2="122" stroke="#D4520E" stroke-width="1" opacity="0.5"/>
                </svg>
            </div>

            <!-- Right Signatures -->
            <div class="w-[30%] text-center space-y-6">
                <div>
                    <div class="w-full h-px bg-[#333333] mb-1"></div>
                    <span class="text-[9px] text-[#333333]">Director</span>
                </div>
                <div>
                    <div class="w-full h-px bg-[#333333] mb-1"></div>
                    <span class="text-[9px] text-[#333333]">Graduate's I.D. No.</span>
                </div>
            </div>
        </div>

        <!-- BOTTOM INFO BAR -->
        <div class="mx-6 mb-4">
            <div class="border border-[#D4A843] rounded-lg bg-[#FFFBF5] p-4">
                <div class="flex justify-between">
                    <!-- Left Column -->
                    <div class="w-[46%] space-y-3">
                        <!-- Student Number -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border-2 border-[#1E3A8A] flex items-center justify-center flex-shrink-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E3A8A" stroke-width="1.5">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                    <path d="M6 12v5c0 1.5 2.5 3 6 3s6-1.5 6-3v-5"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[7px] font-bold text-[#1E3A8A] uppercase tracking-wide">Student Number</div>
                                <div class="text-[13px] font-bold text-black">{{ $student_number ?? '26Edu249580' }}</div>
                            </div>
                        </div>
                        <!-- Certificate Number -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border-2 border-[#1E3A8A] flex items-center justify-center flex-shrink-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E3A8A" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <path d="M3 9h18"/>
                                    <path d="M9 21V9"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[7px] font-bold text-[#1E3A8A] uppercase tracking-wide">Certificate Number</div>
                                <div class="text-[13px] font-bold text-black">{{ $certificate_number ?? 'NRC 2495807/1/1' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Center Divider -->
                    <div class="w-[8%] flex items-center justify-center">
                        <div class="flex flex-col items-center">
                            <div class="w-px h-4 bg-[#D4A843]"></div>
                            <span class="text-[#D4A843] text-xs py-0.5">&#9670;</span>
                            <div class="w-px h-4 bg-[#D4A843]"></div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="w-[46%] space-y-3">
                        <!-- Date of Graduation -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border-2 border-[#1E3A8A] flex items-center justify-center flex-shrink-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E3A8A" stroke-width="1.5">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                                    <path d="M16 2v4M8 2v4M3 10h18"/>
                                    <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[7px] font-bold text-[#1E3A8A] uppercase tracking-wide">Date of Graduation</div>
                                <div class="text-[13px] font-bold text-black">{{ ($graduation_day ?? '27') . ($graduation_suffix ?? 'th') . ' ' . ($graduation_month ?? 'March') . ' ' . ($graduation_year ?? '2026') }}</div>
                            </div>
                        </div>
                        <!-- Course -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border-2 border-[#1E3A8A] flex items-center justify-center flex-shrink-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E3A8A" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="6"/>
                                    <path d="M15.5 6.5L12 10l-1.5-1.5"/>
                                    <path d="M12 14v4M8 18h8"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-[7px] font-bold text-[#1E3A8A] uppercase tracking-wide">Course</div>
                                <div class="text-[13px] font-bold text-black">{{ $course_title ?? 'General Basic Computing' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Diamonds -->
        <div class="text-center pb-4">
            <span class="text-[#D4A843] text-[10px] tracking-[4px]">&#9670; &#9670; &#9670;</span>
        </div>

    </div>
</div>

</body>
</html>
