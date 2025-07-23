import { Metadata } from 'next';
import Link from 'next/link';

export const metadata: Metadata = {
  title: 'Documentation | Locus',
  description: 'Learn how to use Locus, the AI-powered navigation platform with voice cloning capabilities.',
};

export default function Documentation() {
  return (
    <div className="min-h-screen bg-white dark:bg-gray-900">
      <div className="px-4 py-12 mx-auto max-w-7xl">
        <div className="grid grid-cols-12 gap-8">
          {/* Sidebar Navigation */}
          <nav className="col-span-3">
            <div className="sticky top-8">
              <h3 className="mb-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">Getting Started</h3>
              <ul className="space-y-2">
                <DocLink href="/docs/introduction" text="Introduction" />
                <DocLink href="/docs/quick-start" text="Quick Start Guide" />
                <DocLink href="/docs/installation" text="Installation" />
              </ul>

              <h3 className="mt-8 mb-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">Core Features</h3>
              <ul className="space-y-2">
                <DocLink href="/docs/navigation" text="Navigation" />
                <DocLink href="/docs/voice-cloning" text="Voice Cloning" />
                <DocLink href="/docs/ai-features" text="AI Features" />
                <DocLink href="/docs/personalization" text="Personalization" />
              </ul>

              <h3 className="mt-8 mb-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">Guides</h3>
              <ul className="space-y-2">
                <DocLink href="/docs/voice-recording" text="Voice Recording Tips" />
                <DocLink href="/docs/offline-use" text="Offline Usage" />
                <DocLink href="/docs/preferences" text="Setting Preferences" />
                <DocLink href="/docs/troubleshooting" text="Troubleshooting" />
              </ul>

              <h3 className="mt-8 mb-4 text-sm font-semibold text-gray-900 dark:text-white uppercase">API Reference</h3>
              <ul className="space-y-2">
                <DocLink href="/docs/api/authentication" text="Authentication" />
                <DocLink href="/docs/api/navigation" text="Navigation API" />
                <DocLink href="/docs/api/voice" text="Voice API" />
                <DocLink href="/docs/api/websockets" text="WebSocket API" />
              </ul>
            </div>
          </nav>

          {/* Main Content */}
          <main className="col-span-9 prose dark:prose-invert max-w-none">
            <h1>Welcome to Locus Documentation</h1>
            
            <p className="lead">
              Locus is an AI-powered navigation platform that combines cutting-edge voice cloning technology 
              with personalized travel guidance. This documentation will help you get started and make the 
              most of Locus's features.
            </p>

            <h2>Key Concepts</h2>
            
            <h3>1. Personalized Navigation</h3>
            <p>
              Locus adapts to your preferences and travel style. Whether you prefer scenic routes, 
              efficient paths, or cultural experiences, our AI system learns and adjusts to provide 
              the perfect guidance for you.
            </p>

            <h3>2. Voice Cloning</h3>
            <p>
              Create your own navigation voice by providing a short voice sample. Our advanced voice 
              cloning technology ensures natural-sounding guidance in your own voice or that of a 
              loved one.
            </p>

            <h3>3. Real-Time AI Insights</h3>
            <p>
              As you travel, Locus provides contextual information about your surroundings, including 
              historical facts, cultural insights, and local recommendations, all tailored to your 
              interests.
            </p>

            <h2>Getting Started</h2>
            
            <ol>
              <li>
                <strong>Create an Account</strong>
                <p>
                  Sign up for Locus using your email address or social media accounts. This allows 
                  us to save your preferences and voice models.
                </p>
              </li>
              <li>
                <strong>Set Your Preferences</strong>
                <p>
                  Configure your language, navigation preferences, and choose your preferred persona 
                  for AI interactions.
                </p>
              </li>
              <li>
                <strong>Record Your Voice</strong>
                <p>
                  Follow our voice recording guide to create your personalized navigation voice. 
                  The process takes only a few minutes.
                </p>
              </li>
              <li>
                <strong>Start Exploring</strong>
                <p>
                  Enter your destination and let Locus guide you with personalized insights and 
                  directions in your chosen voice.
                </p>
              </li>
            </ol>

            <div className="p-4 mt-8 bg-blue-50 dark:bg-blue-900 rounded-lg">
              <h3 className="mt-0">Need Help?</h3>
              <p className="mb-0">
                If you need assistance, check out our <Link href="/docs/troubleshooting">troubleshooting guide</Link> or 
                contact our support team at <a href="mailto:support@locus.app">support@locus.app</a>.
              </p>
            </div>
          </main>
        </div>
      </div>
    </div>
  );
}

function DocLink({ href, text }: { href: string; text: string }) {
  return (
    <li>
      <Link
        href={href}
        className="nav-link block relative overflow-hidden group"
      >
        <span className="relative z-10">{text}</span>
        <div className="absolute inset-0 bg-[var(--color-primary)] opacity-0 transform -translate-x-full group-hover:translate-x-0 group-hover:opacity-5 transition-all duration-300"></div>
      </Link>
    </li>
  );
}
