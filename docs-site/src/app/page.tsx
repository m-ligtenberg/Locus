import { motion } from 'framer-motion';
import { ArrowRightIcon, MapIcon, MicrophoneIcon, GlobeAltIcon, SparklesIcon } from '@heroicons/react/24/outline';
import Link from 'next/link';

export default function Home() {
  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="container glass-panel mt-8">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          className="text-center py-24"
        >
          <h1 className="mb-8 text-6xl font-light tracking-tight accent-border inline-block">
            Your AI Travel Companion
            <span className="text-[var(--color-primary)]"> with a Personal Touch</span>
          </h1>
          <p className="mx-auto mb-12 text-xl text-[var(--color-text-secondary)] max-w-3xl leading-relaxed">
            Discover the world with Locus, an AI-powered navigation platform that adapts to your style. 
            Experience personalized guidance with voice cloning and real-time local insights.
          </p>
          <div className="flex justify-center gap-4">
            <Link href="/get-started" 
              className="px-8 py-3 text-lg font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
              Get Started
            </Link>
            <Link href="/docs" 
              className="px-8 py-3 text-lg font-medium text-blue-600 border-2 border-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors">
              Documentation
            </Link>
          </div>
        </motion.div>
      </section>

      {/* Features Section */}
      <section className="px-4 py-20 mx-auto max-w-7xl bg-gray-50 dark:bg-gray-800">
        <h2 className="mb-12 text-4xl font-bold text-center text-gray-900 dark:text-white">
          Key Features
        </h2>
        <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
          <FeatureCard
            icon={<SparklesIcon className="w-8 h-8" />}
            title="AI-Powered Navigation"
            description="Smart routing and real-time insights adapted to your preferences and travel style."
          />
          <FeatureCard
            icon={<MicrophoneIcon className="w-8 h-8" />}
            title="Voice Cloning"
            description="Create your personalized navigation voice for a more natural and engaging experience."
          />
          <FeatureCard
            icon={<GlobeAltIcon className="w-8 h-8" />}
            title="Multi-Language"
            description="Support for multiple languages and regional accents for global accessibility."
          />
          <FeatureCard
            icon={<MapIcon className="w-8 h-8" />}
            title="Real-Time Updates"
            description="Live traffic, weather, and point-of-interest updates as you travel."
          />
        </div>
      </section>

      {/* How It Works Section */}
      <section className="px-4 py-20 mx-auto max-w-7xl">
        <h2 className="mb-12 text-4xl font-bold text-center text-gray-900 dark:text-white">
          How It Works
        </h2>
        <div className="grid gap-8 md:grid-cols-3">
          <StepCard
            number="1"
            title="Create Your Profile"
            description="Set up your account and customize your preferences, including language and navigation style."
          />
          <StepCard
            number="2"
            title="Record Your Voice"
            description="Provide a short voice sample to create your personalized navigation voice."
          />
          <StepCard
            number="3"
            title="Start Exploring"
            description="Enter your destination and let Locus guide you with personalized insights and directions."
          />
        </div>
      </section>

      {/* CTA Section */}
      <section className="px-4 py-20 mx-auto max-w-7xl bg-blue-600 dark:bg-blue-700">
        <div className="text-center text-white">
          <h2 className="mb-8 text-4xl font-bold">Ready to Transform Your Travel Experience?</h2>
          <p className="mb-8 text-xl">Join thousands of travelers who have already discovered the future of navigation.</p>
          <Link href="/signup" 
            className="inline-flex items-center px-8 py-3 text-lg font-medium text-blue-600 bg-white rounded-lg hover:bg-gray-100 transition-colors">
            Get Started Now <ArrowRightIcon className="w-5 h-5 ml-2" />
          </Link>
        </div>
      </section>
    </div>
  );
}

function FeatureCard({ icon, title, description }) {
  return (
    <motion.div
      whileHover={{ y: -5 }}
      className="glass-panel p-8 relative overflow-hidden group"
    >
      <div className="absolute top-0 left-0 w-1 h-full bg-[var(--color-primary)] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div className="mb-6 text-[var(--color-primary)] transform group-hover:scale-110 transition-transform duration-300">{icon}</div>
      <h3 className="mb-3 text-xl font-light tracking-wide">{title}</h3>
      <p className="text-[var(--color-text-secondary)] leading-relaxed">{description}</p>
    </motion.div>
  );
}

function StepCard({ number, title, description }) {
  return (
    <motion.div
      whileHover={{ scale: 1.02 }}
      className="p-6 bg-gray-50 dark:bg-gray-700 rounded-xl"
    >
      <div className="flex items-center mb-4">
        <span className="flex items-center justify-center w-10 h-10 text-xl font-bold text-white bg-blue-600 rounded-full">
          {number}
        </span>
      </div>
      <h3 className="mb-2 text-xl font-semibold text-gray-900 dark:text-white">{title}</h3>
      <p className="text-gray-600 dark:text-gray-300">{description}</p>
    </motion.div>
  );
}
