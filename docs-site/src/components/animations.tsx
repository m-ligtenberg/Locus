import { motion } from 'framer-motion';

export const animations = {
  slideIn: {
    initial: { opacity: 0, x: -20 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: 20 }
  },
  fadeIn: {
    initial: { opacity: 0 },
    animate: { opacity: 1 },
    exit: { opacity: 0 }
  },
  scaleIn: {
    initial: { opacity: 0, scale: 0.9 },
    animate: { opacity: 1, scale: 1 },
    exit: { opacity: 0, scale: 0.9 }
  },
  popIn: {
    initial: { opacity: 0, scale: 0.8, y: 20 },
    animate: { opacity: 1, scale: 1, y: 0 },
    exit: { opacity: 0, scale: 0.8, y: 20 }
  }
};

export const transitions = {
  spring: {
    type: "spring",
    stiffness: 300,
    damping: 30
  },
  smooth: {
    duration: 0.4,
    ease: [0.43, 0.13, 0.23, 0.96]
  },
  bounce: {
    type: "spring",
    stiffness: 400,
    damping: 10
  }
};

export const hover = {
  scale: {
    scale: 1.05,
    transition: {
      duration: 0.2
    }
  },
  lift: {
    y: -5,
    transition: {
      duration: 0.2
    }
  },
  glow: {
    boxShadow: "0 0 20px rgba(0,118,255,0.2)",
    transition: {
      duration: 0.2
    }
  }
};

export function AeroButton({ children, onClick, variant = "primary" }) {
  return (
    <motion.button
      whileHover={hover.scale}
      whileTap={{ scale: 0.95 }}
      className={`btn btn-${variant}`}
      onClick={onClick}
    >
      {children}
    </motion.button>
  );
}

export function FloatingElement({ children, delay = 0 }) {
  return (
    <motion.div
      animate={{
        y: [0, -10, 0],
      }}
      transition={{
        duration: 3,
        delay,
        repeat: Infinity,
        ease: "easeInOut"
      }}
    >
      {children}
    </motion.div>
  );
}

export function ParallaxContainer({ children, speed = 1 }) {
  return (
    <motion.div
      initial={{ y: 0 }}
      style={{
        y: speed * window.scrollY
      }}
    >
      {children}
    </motion.div>
  );
}

export function GlowingBorder({ children }) {
  return (
    <motion.div
      whileHover={{
        boxShadow: [
          "0 0 0 rgba(0,118,255,0)",
          "0 0 20px rgba(0,118,255,0.2)",
          "0 0 40px rgba(0,118,255,0.1)",
          "0 0 20px rgba(0,118,255,0.2)"
        ]
      }}
      transition={{
        duration: 1,
        repeat: Infinity
      }}
    >
      {children}
    </motion.div>
  );
}

export function RippleEffect({ children }) {
  return (
    <motion.div
      whileTap={{
        boxShadow: [
          "0 0 0 0 rgba(0,118,255,0.4)",
          "0 0 0 20px rgba(0,118,255,0)",
        ]
      }}
      transition={{
        duration: 0.5
      }}
    >
      {children}
    </motion.div>
  );
}
