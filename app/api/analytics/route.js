import { sql } from '@vercel/postgres';
import { NextResponse } from 'next/server';

export async function POST(req) {
  try {
    const { event_type, page } = await req.json();
    await sql`INSERT INTO analytics (event_type, page) VALUES (${event_type}, ${page||'/'})`;
    return NextResponse.json({ success: true });
  } catch {
    return NextResponse.json({ success: false });
  }
}

export async function GET() {
  try {
    const views = await sql`SELECT COUNT(*) as count FROM analytics WHERE event_type = 'page_view'`;
    const contacts = await sql`SELECT COUNT(*) as count FROM analytics WHERE event_type = 'contact_submit'`;
    const whatsapp = await sql`SELECT COUNT(*) as count FROM analytics WHERE event_type = 'whatsapp_click'`;
    const cv = await sql`SELECT COUNT(*) as count FROM analytics WHERE event_type = 'cv_download'`;
    return NextResponse.json({
      page_views: views.rows[0].count,
      contact_submits: contacts.rows[0].count,
      whatsapp_clicks: whatsapp.rows[0].count,
      cv_downloads: cv.rows[0].count,
    });
  } catch {
    return NextResponse.json({ page_views: 0, contact_submits: 0, whatsapp_clicks: 0, cv_downloads: 0 });
  }
}
