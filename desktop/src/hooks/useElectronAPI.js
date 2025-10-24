import { useEffect, useState } from 'react';

export const useElectronAPI = () => {
  const [electronAPI, setElectronAPI] = useState(null);

  useEffect(() => {
    if (window.electronAPI) {
      setElectronAPI(window.electronAPI);
    }
  }, []);

  return electronAPI;
};
