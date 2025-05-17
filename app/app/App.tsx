import React from 'react';
import RootNavigator from './navigation/RootNavigator';
import AuthProvider from './context/AuthContext';
import { View } from 'react-native';
import { Text } from '@react-navigation/elements';

const App = () => {
  return (
    <AuthProvider>
      <RootNavigator />
    </AuthProvider>
  );
};

export default App;