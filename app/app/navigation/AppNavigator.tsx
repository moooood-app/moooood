import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import EntriesScreen from '@/app/screens/EntriesScreen';
import MetricsScreen from '@/app/screens/MetricsScreen';

const Stack = createNativeStackNavigator();

export default function AppNavigator() {
  return (
    <Stack.Navigator>
      <Stack.Screen name="Entries" component={EntriesScreen} />
      <Stack.Screen name="Metrics" component={MetricsScreen} />
    </Stack.Navigator>
  );
}