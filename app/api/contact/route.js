import { sql } from '@vercel/postgres';
import { NextResponse } from 'next/server';

export async function POST(req) {
  try {
    const { name, email, message } = await req.json();
    if (!name || !email) return NextResponse.json({ error: 'Champs requis manquants' }, { status: 400 });

    await sql`INSERT INTO contacts (name, email, message) VALUES (${name}, ${email}, ${message || ''})`;
    return NextResponse.json({ success: true });
  } catch (err) {
    console.error('contact POST:', err);
    return NextResponse.json({ error: 'Erreur serveur' }, { status: 500 });
  }
}

export async function GET() {
  try {
    const { rows } = await sql`SELECT * FROM contacts ORDER BY created_at DESC`;
    return NextResponse.json(rows);
  } catch (err) {
    return NextResponse.json({ error: 'Erreur serveur' }, { status: 500 });
  }
}
