import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { cn } from '@/lib/utils';

interface GlassPanelProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: 'primary' | 'accent' | 'dark' | 'light';
  blur?: 'none' | 'sm' | 'md' | 'lg';
  intensity?: 'low' | 'medium' | 'high';
  border?: boolean;
  hoverable?: boolean;
  children: React.ReactNode;
}

const blurValues = {
  none: 'backdrop-blur-none',
  sm: 'backdrop-blur-sm',
  md: 'backdrop-blur-md',
  lg: 'backdrop-blur-lg'
};

const intensityValues = {
  low: 'bg-opacity-30 dark:bg-opacity-20',
  medium: 'bg-opacity-50 dark:bg-opacity-40',
  high: 'bg-opacity-70 dark:bg-opacity-60'
};

export const GlassPanel = ({
  variant = 'light',
  blur = 'md',
  intensity = 'medium',
  border = true,
  hoverable = false,
  className,
  children,
  ...props
}: GlassPanelProps) => {
  const baseStyles = cn(
    'rounded-xl transition-all duration-300',
    blurValues[blur],
    intensityValues[intensity],
    {
      'border border-white/20 dark:border-white/10': border,
      'hover:bg-opacity-80 hover:dark:bg-opacity-70 hover:translate-y-[-2px] hover:shadow-lg':
        hoverable,
      'bg-white dark:bg-gray-900': variant === 'light',
      'bg-primary-500': variant === 'primary',
      'bg-accent-500': variant === 'accent',
      'bg-gray-900 dark:bg-white': variant === 'dark'
    },
    className
  );

  return (
    <motion.div
      className={baseStyles}
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: 20 }}
      transition={{ duration: 0.3 }}
      {...props}
    >
      {children}
    </motion.div>
  );
};

interface GlassCardProps extends GlassPanelProps {
  title?: string;
  subtitle?: string;
  icon?: React.ReactNode;
  action?: React.ReactNode;
}

export const GlassCard = ({
  title,
  subtitle,
  icon,
  action,
  children,
  ...props
}: GlassCardProps) => {
  return (
    <GlassPanel hoverable {...props}>
      <div className="p-6">
        {(title || icon) && (
          <div className="flex items-center gap-4 mb-4">
            {icon && (
              <div className="text-primary-500 dark:text-primary-400">{icon}</div>
            )}
            {title && (
              <div>
                <h3 className="text-xl font-light tracking-wide text-gray-900 dark:text-white">
                  {title}
                </h3>
                {subtitle && (
                  <p className="text-sm text-gray-600 dark:text-gray-300">
                    {subtitle}
                  </p>
                )}
              </div>
            )}
          </div>
        )}
        <div className="space-y-4">{children}</div>
        {action && <div className="mt-6">{action}</div>}
      </div>
    </GlassPanel>
  );
};

interface GlassModalProps extends GlassPanelProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
}

export const GlassModal = ({
  isOpen,
  onClose,
  title,
  children,
  ...props
}: GlassModalProps) => {
  return (
    <AnimatePresence>
      {isOpen && (
        <>
          <motion.div
            className="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onClose}
          />
          <motion.div
            className="fixed inset-0 z-50 flex items-center justify-center p-4"
            initial={{ scale: 0.95, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            exit={{ scale: 0.95, opacity: 0 }}
            transition={{ duration: 0.2 }}
          >
            <GlassPanel
              className="w-full max-w-lg m-auto"
              intensity="high"
              {...props}
            >
              <div className="p-6">
                {title && (
                  <h2 className="mb-4 text-2xl font-light tracking-wide">
                    {title}
                  </h2>
                )}
                {children}
              </div>
            </GlassPanel>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
};
