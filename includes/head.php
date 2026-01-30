<head>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA_TRACKING_ID; ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo GA_TRACKING_ID; ?>');
  </script>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?></title>
  <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">

  <link rel="canonical" href="<?php echo SITE_URL; ?>/" />

  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-precomposed.png">

  <link rel="preconnect" href="https://cdn.tailwindcss.com">
  <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">

  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo SITE_URL; ?>/">
  <meta property="og:title" content="<?php echo SITE_NAME; ?>">
  <meta property="og:description" content="Oversigt over danske tech-systemer som alternativer til store internationale spillere. Få inspiration til din tech stack med danske SaaS-løsninger.">
  <meta property="og:image" content="<?php echo OG_IMAGE; ?>">
  <meta property="og:locale" content="<?php echo SITE_LOCALE; ?>">
  <meta property="og:site_name" content="Dansk Tech Stack">

  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?php echo SITE_URL; ?>/">
  <meta property="twitter:title" content="<?php echo SITE_NAME; ?>">
  <meta property="twitter:description" content="Oversigt over danske tech-systemer som alternativer til store internationale spillere.">
  <meta property="twitter:image" content="<?php echo OG_IMAGE; ?>">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "<?php echo SITE_NAME; ?>",
    "description": "<?php echo SITE_DESCRIPTION; ?>",
    "url": "<?php echo SITE_URL; ?>",
    "publisher": {
      "@type": "Organization",
      "name": "<?php echo PUBLISHER_NAME; ?>",
      "url": "<?php echo PUBLISHER_URL; ?>"
    },
    "inLanguage": "da-DK"
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Danske tech-systemer",
    "description": "Liste over danske alternativer til internationale tech-systemer",
    "itemListElement": [
      {
        "@type": "SoftwareApplication",
        "name": "Alunta.com",
        "description": "Abonnementer, betalinger og fakturering",
        "url": "https://alunta.com",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "GrowPanel.io",
        "description": "Subscription analytics platform",
        "url": "https://growpanel.io",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "PingPuffin.com",
        "description": "Uptime monitoring og alerts",
        "url": "https://pingpuffin.com",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Sleeknote.com",
        "description": "Email marketing og popups",
        "url": "https://sleeknote.com",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Clerk.io",
        "description": "Personalisering og anbefalinger",
        "url": "https://clerk.io",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "PageVitals.com",
        "description": "Performance monitoring",
        "url": "https://pagevitals.com",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Herodesk.io",
        "description": "Customer support og helpdesk",
        "url": "https://herodesk.io",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Morningscore.io",
        "description": "SEO + GEO tracking",
        "url": "https://morningscore.io",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Dreamdata.io",
        "description": "B2B revenue attribution",
        "url": "https://dreamdata.io",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Timelog.com",
        "description": "Tidsregistrering, ressourcestyring og fakturering",
        "url": "https://timelog.com",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Fenerum.com",
        "description": "SaaS finances",
        "url": "https://fenerum.com",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "E-conomic.dk",
        "description": "Regnskab og bogholderi",
        "url": "https://e-conomic.dk",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Dinero.dk",
        "description": "Regnskab og bogholderi",
        "url": "https://dinero.dk",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Plecto.com",
        "description": "Performance management og dashboards",
        "url": "https://plecto.com",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "Updatemate.ai",
        "description": "Automatisering og integrationer",
        "url": "https://updatemate.ai",
        "applicationCategory": "BusinessApplication"
      },
      {
        "@type": "SoftwareApplication",
        "name": "SEO.ai",
        "description": "Automatisering af indholdsproduktion",
        "url": "https://seo.ai",
        "applicationCategory": "BusinessApplication"
      }
    ]
  }
  </script>
</head>
