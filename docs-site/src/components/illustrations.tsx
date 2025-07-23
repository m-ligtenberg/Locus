import React from 'react';
import { motion } from 'framer-motion';

export const AeroIllustrations = {
  Waypoint: () => (
    <motion.svg
      width="200"
      height="200"
      viewBox="0 0 200 200"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      initial="hidden"
      animate="visible"
    >
      <motion.circle
        cx="100"
        cy="100"
        r="80"
        stroke="var(--color-primary-300)"
        strokeWidth="2"
        fill="var(--color-glass-primary)"
        variants={{
          hidden: { scale: 0.8, opacity: 0 },
          visible: {
            scale: 1,
            opacity: 1,
            transition: { duration: 0.5 }
          }
        }}
      />
      <motion.path
        d="M100 50v100M50 100h100"
        stroke="var(--color-primary-500)"
        strokeWidth="2"
        strokeLinecap="round"
        variants={{
          hidden: { pathLength: 0, opacity: 0 },
          visible: {
            pathLength: 1,
            opacity: 1,
            transition: { duration: 1, delay: 0.5 }
          }
        }}
      />
    </motion.svg>
  ),

  Navigation: () => (
    <motion.svg
      width="200"
      height="200"
      viewBox="0 0 200 200"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      initial="hidden"
      animate="visible"
    >
      <motion.circle
        cx="100"
        cy="100"
        r="60"
        stroke="var(--color-primary-300)"
        strokeWidth="2"
        fill="none"
        variants={{
          hidden: { scale: 0.8, opacity: 0 },
          visible: {
            scale: 1,
            opacity: 1,
            transition: { duration: 0.5 }
          }
        }}
      />
      <motion.path
        d="M100 40l30 120-30-30-30 30z"
        fill="var(--color-primary-500)"
        variants={{
          hidden: { scale: 0.8, opacity: 0 },
          visible: {
            scale: 1,
            opacity: 1,
            transition: { duration: 0.5, delay: 0.3 }
          }
        }}
      />
    </motion.svg>
  ),

  Voice: () => (
    <motion.svg
      width="200"
      height="200"
      viewBox="0 0 200 200"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      initial="hidden"
      animate="visible"
    >
      {[20, 60, 100, 140, 180].map((x, i) => (
        <motion.rect
          key={i}
          x={x}
          y="60"
          width="8"
          height="80"
          rx="4"
          fill="var(--color-primary-500)"
          variants={{
            hidden: { scaleY: 0, opacity: 0 },
            visible: {
              scaleY: [0.2, 1, 0.2],
              opacity: 1,
              transition: {
                duration: 1,
                repeat: Infinity,
                delay: i * 0.1
              }
            }
          }}
        />
      ))}
    </motion.svg>
  ),

  Location: () => (
    <motion.svg
      width="200"
      height="200"
      viewBox="0 0 200 200"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      initial="hidden"
      animate="visible"
    >
      <motion.path
        d="M100 20c-33.137 0-60 26.863-60 60 0 45 60 100 60 100s60-55 60-100c0-33.137-26.863-60-60-60zm0 80a20 20 0 110-40 20 20 0 010 40z"
        fill="var(--color-primary-500)"
        variants={{
          hidden: { scale: 0.8, opacity: 0 },
          visible: {
            scale: 1,
            opacity: 1,
            transition: { duration: 0.5 }
          }
        }}
      />
      <motion.circle
        cx="100"
        cy="80"
        r="40"
        stroke="var(--color-primary-300)"
        strokeWidth="2"
        fill="none"
        strokeDasharray="251.2"
        variants={{
          hidden: { strokeDashoffset: 251.2 },
          visible: {
            strokeDashoffset: 0,
            transition: { duration: 1, delay: 0.5 }
          }
        }}
      />
    </motion.svg>
  )
};
