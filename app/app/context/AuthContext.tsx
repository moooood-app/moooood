import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { getItem, setItem, removeItem } from '../utils/storage';

interface AuthContextType {
    isLoggedIn: boolean;
    login: (email: string, password: string) => Promise<void>;
    logout: () => void;
    isLoading: boolean;
}

export const AuthContext = createContext({
    isLoggedIn: false,
    login: async (email: string, password: string) => {},
    logout: () => {},
    isLoading: true,
});

const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [isLoggedIn, setIsLoggedIn] = useState(false);
    const [isLoading, setLoading] = useState(true);

    useEffect(() => {
        const initializeAuth = async () => {
            try {
                const storedToken = await getItem('token');
                setIsLoggedIn(!!storedToken);
            } catch (error) {
                console.error('Error initializing auth:', error);
            } finally {
                setLoading(false);
            }
        };

        initializeAuth();
    }, []);

    const login = async (email: string, password: string) => {
        try {
            const response = await fetch("http://localhost/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    email: email,
                    password: password,
                }),
            });

            if (response.ok) {
                const data = await response.json();
                setItem('token', data.token);
                setItem('refreshToken', data.token);
                setIsLoggedIn(true);

                return;
            }

            throw new Error("Invalid email or password");
        } catch (error) {
            if (error instanceof Error) {
                throw new Error(error.message);
            }
            throw new Error('An unknown error occurred');
        }
    };

    const logout = () => {
        removeItem('token');
        removeItem('refreshToken');
        setIsLoggedIn(false);
    };

    return (
        <AuthContext.Provider value={{ isLoggedIn, login, logout, isLoading: isLoading }}>
            {children}
        </AuthContext.Provider>
    );
};

// Hook to use AuthContext with proper types
export const useAuth = (): AuthContextType => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};

export default AuthProvider;