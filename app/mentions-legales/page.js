'use client';
import { useEffect } from 'react';
import Link from 'next/link';

const SECTIONS = [
  {
    title: '1. Éditeur du site',
    content: `Nom : Exaucé Joël Attinganmè
Activité : Développeur web freelance & Monteur vidéo
Statut : Auto-entrepreneur / Freelance indépendant
Localisation : Abomey-Calavi, République du Bénin
Email : Exaucejoel29@gmail.com
Téléphone : +229 01 49 51 40 64`,
  },
  {
    title: '2. Directeur de la publication',
    content: `Le directeur de la publication est Exaucé Joël Attinganmè, responsable de l'ensemble des contenus publiés sur ce site.`,
  },
  {
    title: '3. Hébergement',
    content: `Ce site est hébergé par Vercel Inc., dont le siège social est situé à :
340 Pine Street, Suite 701
San Francisco, CA 94104
États-Unis
Site : https://vercel.com`,
  },
  {
    title: '4. Propriété intellectuelle',
    content: `L'ensemble des contenus présents sur ce site (textes, visuels, code, design, logos, illustrations) sont la propriété exclusive d'Exaucé Joël Attinganmè ou de leurs auteurs respectifs.

Toute reproduction, distribution, modification ou utilisation commerciale, même partielle, sans autorisation écrite préalable est strictement interdite et peut constituer une contrefaçon au sens des lois applicables.`,
  },
  {
    title: '5. Liens hypertextes',
    content: `Ce site peut contenir des liens vers des sites tiers. Ces liens sont fournis à titre informatif uniquement. Exaucé Attinganmè ne saurait être tenu responsable du contenu, de la disponibilité ou de la politique de confidentialité des sites vers lesquels ces liens pointent.`,
  },
  {
    title: '6. Limitation de responsabilité',
    content: `Les informations contenues sur ce site sont fournies de bonne foi et à titre indicatif. Exaucé Attinganmè ne saurait garantir l'exactitude, la complétude ou l'actualité des informations publiées et décline toute responsabilité en cas d'erreur ou d'omission.

Le site peut être temporairement indisponible pour des raisons de maintenance. Nous ne saurions être tenus responsables des interruptions de service.`,
  },
  {
    title: '7. Droit applicable',
    content: `Le présent site est soumis au droit en vigueur en République du Bénin. Tout litige relatif à l'utilisation de ce site sera soumis à la compétence exclusive des juridictions béninoises.`,
  },
  {
    title: '8. Contact',
    content: `Pour toute question relative à ces mentions légales ou à l'utilisation de ce site :

Email : Exaucejoel29@gmail.com
WhatsApp : +229 01 49 51 40 64
Adresse : Abomey-Calavi, Bénin`,
  },
];

export default function MentionsLegales() {
  useEffect(() => {
    const nav = document.getElementById('ml-nav');
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
        #ml-nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:1.2rem 5%;border-bottom:1px solid transparent;transition:background .5s,border-color .5s}
        #ml-nav.scrolled{background:rgba(0,0,0,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-color:var(--border)}
        .ml-logo{font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--white);text-decoration:none;letter-spacing:-.01em}
        .ml-logo em{font-style:normal;color:var(--blue)}
        .ml-nav-links{display:flex;gap:2rem;list-style:none}
        .ml-nav-links a{font-size:.78rem;color:var(--muted);text-decoration:none;transition:color .2s}
        .ml-nav-links a:hover{color:var(--white)}
        .ml-hero{padding:10rem 5% 4rem;border-bottom:1px solid var(--border)}
        .ml-eyebrow{font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;color:var(--blue);margin-bottom:1.5rem;display:block}
        .ml-hero h1{font-family:'Syne',sans-serif;font-size:clamp(2.5rem,5vw,4rem);font-weight:800;letter-spacing:-.03em;line-height:1;margin-bottom:1rem}
        .ml-hero p{font-size:.85rem;color:var(--muted);margin-top:.75rem}
        .ml-body{max-width:760px;margin:0 auto;padding:5rem 5%}
        .ml-section{margin-bottom:3rem;padding-bottom:3rem;border-bottom:1px solid var(--border)}
        .ml-section:last-child{border-bottom:none}
        .ml-section h2{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;letter-spacing:-.01em;margin-bottom:1.2rem;color:var(--white)}
        .ml-section p{font-size:.85rem;color:var(--muted);line-height:1.9;white-space:pre-line}
        .ml-date{font-size:.75rem;color:rgba(245,245,247,.25);margin-top:2rem;font-style:italic}
        footer{padding:2.5rem 5%;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
        .footer-logo{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:rgba(245,245,247,.4);text-decoration:none}
        .footer-logo em{font-style:normal;color:var(--blue)}
        .footer-links{display:flex;gap:1.5rem;flex-wrap:wrap}
        .footer-links a{font-size:.72rem;color:rgba(245,245,247,.3);text-decoration:none;transition:color .2s}
        .footer-links a:hover{color:var(--muted)}
        .footer-copy{font-size:.68rem;color:rgba(245,245,247,.15);letter-spacing:.04em}
        @media(max-width:768px){
          .ml-nav-links{display:none}
          .ml-hero{padding:8rem 5% 3rem}
          .ml-body{padding:3rem 5%}
          footer{flex-direction:column;align-items:flex-start;gap:1rem}
          .footer-links{flex-direction:column;gap:.6rem}
        }
      `}</style>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet" />

      <nav id="ml-nav">
        <Link href="/" className="ml-logo">EX<em>.</em>AUCÉ</Link>
        <ul className="ml-nav-links">
          <li><Link href="/#about">À propos</Link></li>
          <li><Link href="/#services">Services</Link></li>
          <li><Link href="/cv">CV & Tarifs</Link></li>
          <li><Link href="/faq">FAQ</Link></li>
          <li><Link href="/#contact">Contact</Link></li>
        </ul>
      </nav>

      <section className="ml-hero">
        <span className="ml-eyebrow">Informations légales</span>
        <h1>Mentions<br/><span style={{ color: 'var(--blue)' }}>légales</span></h1>
        <p>Dernière mise à jour : Juin 2025</p>
      </section>

      <div className="ml-body">
        {SECTIONS.map((s, i) => (
          <div key={i} className="ml-section">
            <h2>{s.title}</h2>
            <p>{s.content}</p>
          </div>
        ))}
        <p className="ml-date">Document établi en Juin 2025 — Abomey-Calavi, Bénin.</p>
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
