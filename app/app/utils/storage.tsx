import { MMKV } from 'react-native-mmkv';

const storage = new MMKV();

export const setItem = (key: string, value: string) => {
  storage.set(key, value);
};

export const getItem = (key: string): string | undefined => {
  return storage.getString(key);
};

export const removeItem = (key: string) => {
  storage.delete(key);
};

export default storage;