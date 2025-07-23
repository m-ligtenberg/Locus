import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import './App.css'

// Import components (simplified version)
const Home = () => <div className="p-8"><h1 className="text-2xl font-bold">Welcome to Locus</h1><p>Your AI-powered travel companion</p></div>
const Chat = () => <div className="p-8"><h1 className="text-2xl font-bold">Chat</h1><p>Chat with your AI travel assistant</p></div>
const Admin = () => <div className="p-8"><h1 className="text-2xl font-bold">Admin Panel</h1><p>Manage voice models and settings</p></div>

const queryClient = new QueryClient()

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <Router>
        <div className="min-h-screen bg-gray-50">
          <nav className="bg-blue-600 text-white p-4">
            <div className="container mx-auto flex justify-between items-center">
              <h1 className="text-xl font-bold">Locus</h1>
              <div className="space-x-4">
                <a href="/" className="hover:underline">Home</a>
                <a href="/chat" className="hover:underline">Chat</a>
                <a href="/admin" className="hover:underline">Admin</a>
              </div>
            </div>
          </nav>
          
          <main className="container mx-auto">
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/chat" element={<Chat />} />
              <Route path="/admin" element={<Admin />} />
            </Routes>
          </main>
        </div>
      </Router>
    </QueryClientProvider>
  )
}

export default App