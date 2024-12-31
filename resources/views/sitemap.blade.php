@php
    echo '<?xml version="1.0" encoding="UTF-8"?>';
@endphp

<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<!-- created with Free Online Sitemap Generator www.xml-sitemaps.com -->


<url>
  <loc>{{ url("https://genbipurwokerto.com") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>daily</changefreq>
  <priority>1.00</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/event") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>daily</changefreq>
  <priority>1.00</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/artikel") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>daily</changefreq>
  <priority>1.00</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/galeri") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>weekly</changefreq>
  <priority>0.90</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/podcast") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.80</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/contact") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.80</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/genbi-point") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.80</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/tentang") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.80</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/organisasi") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.80</priority>
</url>

<url>
  <loc>{{ url("https://genbipurwokerto.com/sejarah-kepengurusan") }}</loc>
  <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.80</priority>
</url>


@foreach ($struktur as $item)
    <url>
        <loc>{{ url("https://genbipurwokerto.com/struktur/". $item->periode ."/". $item->jabatan) }}</loc>
        <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.80</priority>
    </url>
@endforeach


@foreach ($galeri as $item)
    <url>
        <loc>{{ url("https://genbipurwokerto.com/galeri/". $item->slug) }}</loc>
        <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.90</priority>
    </url>
@endforeach

@foreach ($event as $item)
    <url>
        <loc>{{ url("https://genbipurwokerto.com/event/". $item->slug) }}</loc>
        <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.90</priority>
    </url>
@endforeach

@foreach ($dataPerPeriodeStruktur as $item)
    <url>
        <loc>{{ url("https://genbipurwokerto.com/struktur/". $item->periode) }}</loc>
        <lastmod>{{ \Carbon\Carbon::parse(date("Y-m-d H:i:s"))->toIso8601String() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.80</priority>
    </url>
@endforeach

@foreach ($artikel as $item)
    <url>
        <loc>{{ url("https://genbipurwokerto.com/artikel/". $item->slug) }}</loc>
        <lastmod>{{
        \Carbon\Carbon::parse($item->updated_at
            ->format("Y-m-d H:i:s"))
            ->toIso8601String() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.00</priority>
    </url>
@endforeach



</urlset>
