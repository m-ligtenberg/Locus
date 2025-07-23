import Link from 'next/link';
import { motion } from 'framer-motion';
import { MoonIcon, SunIcon } from '@heroicons/react/24/outline';
import { useTheme } from 'next-themes';

export function Header() {
  const { theme, setTheme } = useTheme();

  return (
    <motion.header
      initial={{ y: -20, opacity: 0 }}
      animate={{ y: 0, opacity: 1 }}
      className="sticky top-0 z-50 glass-panel backdrop-blur-md"
    >
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link href="/" className="flex items-center space-x-2">
            <motion.div
              className="w-8 h-8 rounded-full bg-[var(--color-primary)]"
              whileHover={{ scale: 1.1 }}
              whileTap={{ scale: 0.95 }}
            />
            <span className="text-xl font-light tracking-wide">Locus</span>
          </Link>

          {/* Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            <NavLink href="/features">Features</NavLink>
            <NavLink href="/docs">Documentation</NavLink>
            <NavLink href="/pricing">Pricing</NavLink>
            <NavLink href="/blog">Blog</NavLink>
          </nav>

          {/* Actions */}
          <div className="flex items-center space-x-4">
            <button
              onClick={() => setTheme(theme === 'dark' ? 'light' : 'dark')}
              className="p-2 rounded-full hover:bg-[var(--color-primary)] hover:bg-opacity-10 transition-colors"
            >
              {theme === 'dark' ? (
                <SunIcon className="w-5 h-5" />
              ) : (
                <MoonIcon className="w-5 h-5" />
              )}
            </button>
            
            <Link 
              href="/login"
              className="nav-link hidden md:inline-block"
            >
              Sign In
            </Link>
            
            <Link 
              href="/signup"
              className="btn btn-primary hidden md:inline-flex"
            >
              Get Started
            </Link>

            {/* Mobile menu button */}
            <button className="p-2 md:hidden">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </motion.header>
  );
}

function NavLink({ href, children }: { href: string; children: React.ReactNode }) {
  return (
    <Link 
      href={href}
      className="nav-link font-light tracking-wide relative group"
    >
      {children}
      <span className="absolute bottom-0 left-0 w-0 h-0.5 bg-[var(--color-primary)] transition-all duration-300 group-hover:w-full" />
    </Link>
  );
}
