import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useAuth } from '../context/AuthContext';
import LoginScreen from '../screens/LoginScreen';
import EntriesScreen from '../screens/EntriesScreen';
import SplashScreen from '../screens/SplashScreen';

const Stack = createNativeStackNavigator();

const RootNavigator = () => {
  const {isLoggedIn, isLoading} = useAuth();
  if (isLoading) {
    return <SplashScreen />;
  }

  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      {!isLoggedIn ? (
        <Stack.Screen
          name="SignIn"
          component={LoginScreen}
          options={{
            title: 'Sign in',
          }}
        />
      ) : (
        <Stack.Screen name="Entries" component={EntriesScreen} />
      )}
    </Stack.Navigator>
  );
};

export default RootNavigator;