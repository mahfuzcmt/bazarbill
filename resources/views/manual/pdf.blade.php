<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>ডিউট্যাপ ব্যবহার সহায়িকা</title>
    <style>
        body { font-family: 'hindsiliguri', 'Hind Siliguri', sans-serif; font-size: 11pt; color: #1e293b; line-height: 1.55; }
        .cover { text-align: center; padding-top: 160pt; }
        .cover .brand { font-size: 40pt; font-weight: bold; color: #4f46e5; }
        .cover h1 { font-size: 26pt; margin: 14pt 0 6pt; color: #0f172a; }
        .cover p { color: #475569; font-size: 12pt; }
        h2 { font-size: 17pt; color: #0f172a; border-bottom: 1.5pt solid #c7d2fe; padding-bottom: 3pt; margin: 22pt 0 8pt; page-break-after: avoid; }
        h3 { font-size: 13pt; color: #312e81; margin: 14pt 0 5pt; page-break-after: avoid; }
        p { margin: 5pt 0; } ul, ol { margin: 4pt 0 4pt 16pt; } li { margin: 2pt 0; }
        table { width: 100%; border-collapse: collapse; margin: 7pt 0; font-size: 10pt; }
        th, td { border: 0.6pt solid #cbd5e1; padding: 4pt 6pt; vertical-align: top; }
        th { background: #eef2ff; color: #312e81; }
        code { color: #4338ca; } em { font-style: normal; color: #4338ca; }
        .toc td { border: 0; padding: 2pt 0; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="brand">DueTap</div>
        <h1>ডিউট্যাপ ব্যবহার সহায়িকা</h1>
        <p>মার্কেটের দোকান ভাড়া ও বকেয়া আদায়ের ডিজিটাল সমাধান</p>
        <p>মার্কেট মালিক · কালেক্টর · দোকানদার</p>
        <p style="margin-top:40pt">সংস্করণ {{ now()->format('Y.m') }} · duetap.com · {{ config('services.support.sales_phone', '01805995662') }}</p>
    </div>
    <pagebreak />
    <h2>সূচিপত্র</h2>
    <table class="toc">
        <tr><td>১. ভূমিকা</td></tr><tr><td>২. শুরু করা</td></tr><tr><td>৩. মার্কেট মালিকের গাইড</td></tr><tr><td>৪. কালেক্টরের গাইড</td></tr>
        <tr><td>৫. দোকানদারের গাইড</td></tr><tr><td>৬. প্ল্যাটফর্ম অ্যাডমিনের গাইড</td></tr><tr><td>৭. সাধারণ সমস্যা ও সমাধান</td></tr><tr><td>৮. সাপোর্ট</td></tr>
    </table>
    <pagebreak />
    @include('manual._content')
</body>
</html>
