'use client';
import { useState, useEffect } from 'react';
import Link from 'next/link';

const FAQS = [
  {
    cat: 'Services & Prestations',
    items: [
      {
        q: 'Quels types de projets prenez-vous en charge ?',
        a: 'Je réalise des sites web (landing pages, sites vitrines, plateformes web), des montages vidéo (YouTube, TikTok, Instagram, Reels), des visuels et thumbnails Canva, ainsi que des packs créateurs complets. Chaque projet est traité sur mesure.',
      },
      {
        q: 'Travaillez-vous avec des clients hors du Bénin ?',
        a: 'Oui, je collabore avec des clients partout en Afrique et dans la diaspora. Tous les échanges se font à distance via WhatsApp, email ou visioconférence. La livraison se fait par lien de téléchargement ou partage de dossier.',
      },
      {
        q: 'Proposez-vous des devis gratuits ?',
        a: 'Absolument. Le devis est 100 % gratuit et sans engagement. Contactez-moi sur WhatsApp ou par email en décrivant votre projet, et je vous reviens avec une estimation claire dans les 24h.',
      },
    ],
  },
  {
    cat: 'Délais & Livraison',
    items: [
      {
        q: 'Quels sont les délais moyens de livraison ?',
        a: 'Les délais varient selon la prestation : visuels et thumbnails en 24 à 48h, montage vidéo court en 3 jours, landing page en 5 jours, site vitrine en 8 à 10 jours. Ces délais sont indicatifs et précisés dans chaque devis.',
      },
      {
        q: 'Comment se déroule la livraison du projet ?',
        a: 'Une fois le projet finalisé, vous recevez les fichiers via un lien WeTransfer, Google Drive ou WhatsApp selon votre préférence. Les fichiers sources sont fournis sur demande.',
      },
      {
        q: 'Des retouches sont-elles incluses ?',
        a: 'Oui. Chaque prestation inclut un cycle de retouches (corrections mineures). Des modifications majeures ou des changements de direction en cours de projet peuvent faire l\'objet d\'un ajustement tarifaire.',
      },
    ],
  },
  {
    cat: 'Paiement & Tarifs',
    items: [
      {
        q: 'Quels sont vos modes de paiement acceptés ?',
        a: 'J\'accepte les paiements par Mobile Money (MTN, Moov), virement bancaire et d\'autres moyens selon votre pays. Le détail est précisé lors de la validation du devis.',
      },
      {
        q: 'Faut-il payer d\'avance ?',
        a: 'Un acompte de 50 % est demandé pour bloquer votre place et démarrer les travaux. Le solde est réglé à la livraison du projet final. Pour les petites prestations (visuels, montages courts), le paiement intégral est demandé à l\'avance.',
      },
      {
        q: 'Les tarifs affichés sont-ils négociables ?',
        a: 'Des réductions sont possibles pour les associations, les étudiants, les volumes importants ou les partenariats long terme. N\'hésitez pas à en discuter lors de votre prise de contact.',
      },
    ],
  },
  {
    cat: 'Technique & Qualité',
    items: [
      {
        q: 'Quels outils et technologies utilisez-vous ?',
        a: 'Pour le web : HTML, CSS, JavaScript, PHP, React, WordPress. Pour la vidéo : CapCut, InShot, Premiere Pro, After Effects. Pour le design : Canva, Figma, Photoshop. Chaque projet utilise l\'outil le mieux adapté à vos besoins.',
      },
      {
        q: 'Les sites que vous créez sont-ils responsive (adaptés au mobile) ?',
        a: 'Oui, tous les sites que je développe sont conçus en mobile-first. Ils s\'adaptent parfaitement aux smartphones, tablettes et ordinateurs de bureau.',
      },
      {
        q: 'Proposez-vous un suivi après livraison ?',
        a: 'Oui. Je propose une maintenance mensuelle qui inclut les mises à jour, corrections, sauvegardes et un support WhatsApp réactif. Un pack maintenance est disponible à partir de 30 €/mois.',
      },
    ],
  },
];

export default function FAQ() {
  const [open, setOpen] = useState({});

  useEffect(() => {
    fetch('/api/analytics', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ event_type: 'page_view', page: '/faq' }) }).catch(() => {});
  }, []);

  function toggle(catIdx, itemIdx) {
    const key = `${catIdx}-${itemIdx}`;
    setOpen(prev => ({ ...prev, [key]: !prev[key] }));
  }

  return (
    <>
      <style>{`
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--bg:#000;--surface:#0d0d0d;--card:#111;--white:#f5f5f7;--muted:rgba(245,245,247,.45);--blue:#0071e3;--border:rgba(245,245,247,.08);--ease:cubic-bezier(.25,.1,.25,1)}
        html{scroll-behavior:smooth}
        body{background:var(--bg);color:var(--white);font-family:'Inter',sans-serif;font-weight:300;line-height:1.6;overflow-x:hidden;-webkit-font-smoothing:antialiased}
        #faq-nav{position:fixed;top:0;left:0;right:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:1.2rem 5%;border-bottom:1px solid transparent;transition:background .5s,border-color .5s}
        #faq-nav.scrolled{background:rgba(0,0,0,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-color:var(--border)}
        .faq-logo{font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;color:var(--white);text-decoration:none;letter-spacing:-.01em}
        .faq-logo em{font-style:normal;color:var(--blue)}
        .faq-nav-links{display:flex;gap:2rem;list-style:none}
        .faq-nav-links a{font-size:.78rem;color:var(--muted);text-decoration:none;transition:color .2s}
        .faq-nav-links a:hover,.faq-nav-links a.active{color:var(--white)}
        .faq-hero{padding:10rem 5% 5rem;border-bottom:1px solid var(--border)}
        .faq-eyebrow{font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;color:var(--blue);margin-bottom:1.5rem;display:block}
        .faq-hero h1{font-family:'Syne',sans-serif;font-size:clamp(3rem,7vw,5.5rem);font-weight:800;letter-spacing:-.03em;line-height:.95;margin-bottom:1.5rem}
        .faq-hero p{font-size:.95rem;color:var(--muted);max-width:520px;line-height:1.8}
        .faq-body{padding:5rem 5%;max-width:860px;margin:0 auto}
        .faq-cat-title{font-family:'Syne',sans-serif;font-size:1.25rem;font-weight:800;letter-spacing:-.01em;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid var(--border);color:var(--white)}
        .faq-cat{margin-bottom:4rem}
        .faq-item{border-bottom:1px solid var(--border)}
        .faq-q{width:100%;background:none;border:none;text-align:left;padding:1.4rem 0;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:1rem;color:var(--white);font-family:'Inter',sans-serif;font-size:.9rem;font-weight:400;transition:color .2s}
        .faq-q:hover{color:var(--blue)}
        .faq-icon{width:20px;height:20px;flex-shrink:0;border:1px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:transform .3s var(--ease),border-color .2s,background .2s}
        .faq-q:hover .faq-icon{border-color:var(--blue)}
        .faq-item.open .faq-icon{transform:rotate(45deg);background:var(--blue);border-color:var(--blue)}
        .faq-icon svg{width:10px;height:10px;fill:var(--white)}
        .faq-a{font-size:.85rem;color:var(--muted);line-height:1.85;max-height:0;overflow:hidden;transition:max-height .4s var(--ease),padding .4s var(--ease)}
        .faq-item.open .faq-a{max-height:200px;padding-bottom:1.4rem}
        .faq-cta{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:3rem;text-align:center;margin-top:4rem}
        .faq-cta h2{font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;letter-spacing:-.02em;margin-bottom:.75rem}
        .faq-cta p{font-size:.85rem;color:var(--muted);margin-bottom:2rem;line-height:1.75}
        .btn-blue{background:var(--blue);color:#fff;border:none;padding:.75rem 1.6rem;font-family:'Inter',sans-serif;font-size:.85rem;font-weight:500;cursor:pointer;text-decoration:none;display:inline-block;border-radius:980px;transition:background .2s,transform .2s}
        .btn-blue:hover{background:#0077ed;transform:translateY(-2px)}
        footer{padding:2.5rem 5%;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
        .footer-logo{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:rgba(245,245,247,.4);text-decoration:none}
        .footer-logo em{font-style:normal;color:var(--blue)}
        .footer-links{display:flex;gap:1.5rem;flex-wrap:wrap}
        .footer-links a{font-size:.72rem;color:rgba(245,245,247,.3);text-decoration:none;transition:color .2s}
        .footer-links a:hover{color:var(--muted)}
        .footer-copy{font-size:.68rem;color:rgba(245,245,247,.15);letter-spacing:.04em}
        @media(max-width:768px){
          .faq-nav-links{display:none}
          .faq-hero{padding:8rem 5% 3rem}
          .faq-body{padding:3rem 5%}
          footer{flex-direction:column;align-items:flex-start;gap:1rem}
          .footer-links{flex-direction:column;gap:.6rem}
        }
      `}</style>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet" />

      <nav id="faq-nav">
        <Link href="/" className="faq-logo">EX<em>.</em>AUCÉ</Link>
        <ul className="faq-nav-links">
          <li><Link href="/#about">À propos</Link></li>
          <li><Link href="/#portfolio">Travaux</Link></li>
          <li><Link href="/#services">Services</Link></li>
          <li><Link href="/cv">CV & Tarifs</Link></li>
          <li><Link href="/faq" className="active">FAQ</Link></li>
        </ul>
      </nav>

      <section className="faq-hero">
        <span className="faq-eyebrow">Questions fréquentes</span>
        <h1>Tout ce que<br/>vous voulez<br/><span style={{color:'var(--blue)'}}>savoir.</span></h1>
        <p>Vous avez une question sur mes services, mes délais, mes tarifs ou ma façon de travailler ? Retrouvez ici les réponses aux questions les plus fréquentes.</p>
      </section>

      <div className="faq-body">
        {FAQS.map((cat, catIdx) => (
          <div key={catIdx} className="faq-cat">
            <div className="faq-cat-title">{cat.cat}</div>
            {cat.items.map((item, itemIdx) => {
              const key = `${catIdx}-${itemIdx}`;
              return (
                <div key={itemIdx} className={`faq-item${open[key] ? ' open' : ''}`}>
                  <button className="faq-q" onClick={() => toggle(catIdx, itemIdx)}>
                    <span>{item.q}</span>
                    <span className="faq-icon">
                      <svg viewBox="0 0 10 10"><path d="M5 1v8M1 5h8"/></svg>
                    </span>
                  </button>
                  <div className="faq-a">{item.a}</div>
                </div>
              );
            })}
          </div>
        ))}

        <div className="faq-cta">
          <h2>Vous n'avez pas trouvé votre réponse ?</h2>
          <p>Contactez-moi directement — je réponds dans les 24h, sans modèle générique.</p>
          <div style={{ display: 'flex', gap: '.75rem', justifyContent: 'center', flexWrap: 'wrap' }}>
            <a href="https://wa.me/22901495140" target="_blank" rel="noopener noreferrer" className="btn-blue">💬 WhatsApp</a>
            <a href="mailto:Exaucejoel29@gmail.com" className="btn-blue" style={{ background: 'rgba(245,245,247,.08)', border: '1px solid rgba(245,245,247,.12)' }}>✉ Email</a>
          </div>
        </div>
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
