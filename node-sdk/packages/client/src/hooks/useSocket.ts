// ==========================================
// USE SOCKET HOOK
// Hook para Socket.IO
// ==========================================

import { useSocketContext } from '../providers/SocketProvider';

/**
 * Hook para acessar socket
 */
export function useSocket() {
  return useSocketContext();
}
