import { redirect } from 'next/navigation';

export default function RootPage() {
  // Redirect to the admin dashboard (handled by the (admin) route group)
  redirect('/dashboard');
}
