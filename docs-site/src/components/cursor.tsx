import React, { useEffect, useState } from 'react';
import { motion, useSpring } from 'framer-motion';

export function AeroCursor() {
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });
  const [isPointer, setIsPointer] = useState(false);

  const springConfig = { damping: 25, stiffness: 700 };
  const cursorX = useSpring(mousePosition.x, springConfig);
  const cursorY = useSpring(mousePosition.y, springConfig);

  useEffect(() => {
    const updateMousePosition = (e: MouseEvent) => {
      setMousePosition({ x: e.clientX, y: e.clientY });
      
      // Check if the cursor is over a clickable element
      const target = e.target as HTMLElement;
      const isClickable = 
        target.tagName.toLowerCase() === 'button' ||
        target.tagName.toLowerCase() === 'a' ||
        target.closest('button') ||
        target.closest('a') ||
        window.getComputedStyle(target).cursor === 'pointer';
      
      setIsPointer(isClickable);
    };

    window.addEventListener('mousemove', updateMousePosition);

    return () => {
      window.removeEventListener('mousemove', updateMousePosition);
    };
  }, []);

  return (
    <>
      {/* Main cursor */}
      <motion.div
        className="fixed pointer-events-none z-50"
        style={{
          left: cursorX,
          top: cursorY,
          translateX: '-50%',
          translateY: '-50%'
        }}
      >
        <motion.div
          className="relative flex items-center justify-center"
          animate={{
            scale: isPointer ? 1.5 : 1,
          }}
          transition={{ duration: 0.15 }}
        >
          {/* Outer ring */}
          <motion.div
            className="absolute rounded-full border border-primary-500"
            animate={{
              width: isPointer ? '30px' : '20px',
              height: isPointer ? '30px' : '20px',
              opacity: 0.5
            }}
            transition={{ duration: 0.15 }}
          />
          
          {/* Inner dot */}
          <motion.div
            className="absolute bg-primary-500 rounded-full"
            animate={{
              width: isPointer ? '4px' : '6px',
              height: isPointer ? '4px' : '6px'
            }}
            transition={{ duration: 0.15 }}
          />
        </motion.div>
      </motion.div>

      {/* Subtle trail effect */}
      <motion.div
        className="fixed pointer-events-none z-40 w-4 h-4 rounded-full bg-primary-500 opacity-20 blur-sm"
        style={{
          left: cursorX,
          top: cursorY,
          translateX: '-50%',
          translateY: '-50%'
        }}
        transition={{ delay: 0.05 }}
      />
    </>
  );
}
