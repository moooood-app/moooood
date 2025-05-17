import React, { useState } from "react";
import { StyleSheet, Text, TextInput, View, Dimensions, Alert, Pressable } from "react-native";
import { useAuth } from "../context/AuthContext";

export default function LoginScreen() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const { login } = useAuth();

  const handleLogin = async () => {
    try {
      await login(email, password); // Call the login function
    } catch (error) {
      Alert.alert("Login Failed", (error as Error).message);
    }
  };

  return (
    <View style={styles.container}>
      <View>
        <Text style={styles.helloText}>Welcome to Moooood</Text>
        <TextInput
          placeholder="email"
          style={styles.textInput}
          value={email}
          onChangeText={setEmail}
        />
        <TextInput
          placeholder="password"
          secureTextEntry
          style={styles.textInput}
          value={password}
          onChangeText={setPassword}
        />
        <Pressable onPress={handleLogin}>
          <Text style={styles.loginBtn}>Login</Text>
        </Pressable>
      </View>
    </View>
  );
}

const screenWidth = Dimensions.get("screen").width;
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#E8E8E2",
    alignItems: "center",
    paddingTop: 250,
  },
  helloText: {
    color: "#33332E",
    marginBottom: 20,
    fontSize: 30,
    textAlign: "center",
  },
  textInput: {
    padding: 5,
    paddingStart: 15,
    backgroundColor: "#FFF",
    width: screenWidth * 0.8,
    borderRadius: 25,
    marginBottom: 15,
    color: "#33332E",
    fontWeight: "600",
  },
  loginBtn: {
    paddingHorizontal: 25,
    paddingVertical: 10,
    backgroundColor: "#16C4DB",
    borderRadius: 25,
    color: "white",
    textAlign: "center",
  },
});
