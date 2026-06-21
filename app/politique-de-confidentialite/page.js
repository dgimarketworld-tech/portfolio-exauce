'use client';
import { useEffect } from 'react';
import Link from 'next/link';

const SECTIONS = [
  {
    title: '1. Responsable du traitement',
    content: `Le responsable du traitement des données collectées via ce site est Exaucé Joël Attinganmè, développeur web et monteur vidéo freelance, domicilié à Abomey-Calavi, Bénin.

Contact : Exaucejoel29@gmail.com — +229 01 49 51 40 64`,
  },
  {
    title: '2. Données collectées',
    content: `Dans le cadre de l'utilisation de ce site, les données suivantes peuvent être collectées :

• Nom et prénom
• Adresse e-mail
• Contenu du message envoyé via le formulaire de contact
• Données de navigation anonymisées (pages visitées, type d'événement) à des fins de statistiques internes

Aucune donnée bancaire, sensible ou biométrique n'est collectée.`,
  },
  {
    title: '3. Finalités du traitement',
    content: `Les données collectées sont utilisées exclusivement pour :

• Répondre aux demandes de contact et de devis
• Assurer le suivi de vos projets en cours
• Améliorer les contenus et l'expérience du site (statistiques anonymes)

Elles ne sont ni revendues, ni transmises à des tiers sans votre consentement explicite.`,
  },
  {
    title: '4. Base légale',
    content: `Le traitement de vos données repose sur votre consentement, matérialisé par l'envoi volontaire du formulaire de contact. Vous êtes libre de ne pas fournir ces informations, sans que cela n'entraîne de conséquence autre que l'impossibilité de vous recontacter.`,
  },
  {
    title: '5. Durée de conservation',
    content: `Vos données sont conservées pour une durée maximale de 24 mois à compter de notre dernier échange. Passé ce délai, elles sont supprimées ou anonymisées.`,
  },
  {
    title: '6. Vos droits',
    content: `Conformément aux réglementations applicables en matière de protection des données personnelles, vous disposez des droits suivants :

• Droit d'accès à vos données
• Droit de rectification
• Droit à l'effacement (droit à l'oubli)
• Droit d'opposition au traitement
• Droit à la portabilité de vos données

Pour exercer ces droits, contactez-nous à : Exaucejoel29@gmail.com`,
  },
  {
    title: '7. Cookies',
    content: `Ce site peut utiliser des cookies techniques nécessaires à son bon fonctionnement (session, préférences). Aucun cookie publicitaire ou de traçage tiers n'est déposé sans votre accord.

Vous pouvez configurer votre navigateur pour refuser les cookies. Cela peut affecter certaines fonctionnalités du site.`,
  },
  {
    title: '8. Sécurité',
    content: `Des mesures techniques et organisationnelles appropriées sont mises en place pour protéger vos données contre toute perte, destruction, altération ou accès non autorisé.`,
  },
  {
    title: '9. Modifications de la politique',
    content: `Cette politique de confidentialité peut être mise à jour à tout moment. La date de dernière mise à jour est indiquée en bas de cette page. Nous vous encourageons à la consulter régulièrement.`,
  },
];

export default function Confidentialite() {
  useEffect(() => {
    const nav = document.getElementById('pc-nav');
    const fn = () => nav?.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', fn, { passive: true });
    return () => window.removeEventListener('scroll', fn);
  }, []);

  return (
    <>
      <style>{`
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--bg:#000;--surface:#0d0d0d;--white:#f5f5f7;--muted:rgba(245,245,247,.45);--blue:#0071e3;--border:rgba(245,245,247,.08);--ease:cubic-bezier(.25,.1,.25,1)}
        html{scroll-behavior:smooth}
        body{background:var(--bg);color:var(--white);font-family:'Inter',sans-serif;font-weight:300;line-height:1.6;overflow-x:hidden;-webkit-font-smoothing:antialiased}
        #pc-nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:1.2rem 5%;border-bottom:1px solid transparent;transition:background .5s,border-color .5s}
        #pc-nav.scrolled{background:rgba(0,0,0,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-color:var(--border)}
        .pc-logo{font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--white);text-decoration:none;letter-spacing:-.01em}
        .pc-logo em{font-style:normal;color:var(--blue)}
        .pc-nav-links{display:flex;gap:2rem;list-style:none}
        .pc-nav-links a{font-size:.78rem;color:var(--muted);text-decoration:none;transition:color .2s}
        .pc-nav-links a:hover{color:var(--white)}
        .pc-hero{padding:10rem 5% 4rem;border-bottom:1px solid var(--border)}
        .pc-eyebrow{font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;color:var(--blue);margin-bottom:1.5rem;display:block}
        .pc-hero h1{font-family:'Syne',sans-serif;font-size:clamp(2.5rem,5vw,4rem);font-weight:800;letter-spacing:-.03em;line-height:1;margin-bottom:1rem}
        .pc-hero p{font-size:.85rem;color:var(--muted);margin-top:.75rem}
        .pc-body{max-width:760px;margin:0 auto;padding:5rem 5%}
        .pc-section{margin-bottom:3rem;padding-bottom:3rem;border-bottom:1px solid var(--border)}
        .pc-section:last-child{border-bottom:none}
        .pc-section h2{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;letter-spacing:-.01em;margin-bottom:1.2rem;color:var(--white)}
        .pc-section p{font-size:.85rem;color:var(--muted);line-height:1.9;white-space:pre-line}
        .pc-date{font-size:.75rem;color:rgba(245,245,247,.25);margin-top:2rem;font-style:italic}
        footer{padding:2.5rem 5%;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
        .footer-logo{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:rgba(245,245,247,.4);text-decoration:none}
        .footer-logo em{font-style:normal;color:var(--blue)}
        .footer-links{display:flex;gap:1.5rem;flex-wrap:wrap}
        .footer-links a{font-size:.72rem;color:rgba(245,245,247,.3);text-decoration:none;transition:color .2s}
        .footer-links a:hover{color:var(--muted)}
        .footer-copy{font-size:.68rem;color:rgba(245,245,247,.15);letter-spacing:.04em}
        @media(max-width:768px){
          .pc-nav-links{display:none}
          .pc-hero{padding:8rem 5% 3rem}
          .pc-body{padding:3rem 5%}
          footer{flex-direction:column;align-items:flex-start;gap:1rem}
          .footer-links{flex-direction:column;gap:.6rem}
        }
      `}</style>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet" />

      <nav id="pc-nav">
        <Link href="/" className="pc-logo">EX<em>.</em>AUCÉ</Link>
        <ul className="pc-nav-links">
          <li><Link href="/#about">À propos</Link></li>
          <li><Link href="/#services">Services</Link></li>
          <li><Link href="/cv">CV & Tarifs</Link></li>
          <li><Link href="/faq">FAQ</Link></li>
          <li><Link href="/#contact">Contact</Link></li>
        </ul>
      </nav>

      <section className="pc-hero">
        <span className="pc-eyebrow">Données personnelles</span>
        <h1>Politique de<br/><span style={{ color: 'var(--blue)' }}>confidentialité</span></h1>
        <p>Dernière mise à jour : Juin 2025</p>
      </section>

      <div className="pc-body">
        {SECTIONS.map((s, i) => (
          <div key={i} className="pc-section">
            <h2>{s.title}</h2>
            <p>{s.content}</p>
          </div>
        ))}
        <p className="pc-date">Document établi en Juin 2025 — Abomey-Calavi, Bénin.</p>
      </div>

      <footer>
        <Link href="/" className="footer-logo">EX<em>.</em>AUCÉ</Link>
        <div className="footer-links">
          <Link href="/">Accueil</Link>
          <Link href="/cv">CV & Tarifs</Link>
          <Link href="/faq">FAQ</Link>
          <Link href="/politique-de-confidentialite">Confidentialité</Link>
          <Link href="/mentions-legales">Mentions légales</Link>
        </div>
        <span className="footer-copy">© 2025 EXAUCÉ · Abomey-Calavi, Bénin</span>
      </footer>
    </>
  );
}
