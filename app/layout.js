import './globals.css';

export const metadata = {
  title: 'EXAUCÉ — Développeur Web & Monteur Vidéo',
  description: 'Portfolio d\'Exaucé Attinganmè — Développeur web front-end et monteur vidéo créatif basé à Abomey-Calavi, Bénin.',
};

export default function RootLayout({ children }) {
  return (
    <html lang="fr">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
      </head>
      <body>{children}</body>
    </html>
  );
}
