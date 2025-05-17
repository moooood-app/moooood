import React, { useEffect, useState, useRef } from 'react';
import { View, Text, FlatList, ActivityIndicator, StyleSheet, ListRenderItem, NativeSyntheticEvent, NativeScrollEvent } from 'react-native';
import { getItem } from '../utils/storage';
import Entry, { EntryInterface } from '../components/Entry';
import { useAuth } from '../context/AuthContext';

const EntriesScreen = () => {
  const [entries, setEntries] = useState<EntryInterface[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const flatListRef = useRef<FlatList<EntryInterface>>(null);

  useEffect(() => {
    fetchEntries();
  }, []);

  useEffect(() => {
    if (!loading && entries.length > 0) {
      setTimeout(() => {
        flatListRef.current?.scrollToEnd({ animated: false });
      }, 0);
    }
  }, [loading, entries]);

  const fetchEntries = async (page = 1) => {
    const token = getItem('token');
    if (!token) {
      return;
    }

    try {
      const response = await fetch(`http://localhost/api/entries?page=${page}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        setEntries(prevEntries => {
          const newEntries = [...prevEntries, ...data.member];
          const uniqueEntries = Array.from(new Set(newEntries.map(entry => entry['@id'])))
            .map(id => newEntries.find(entry => entry['@id'] === id));
          uniqueEntries.sort((a, b) => new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime());
          return uniqueEntries;
        });
        setHasMore(data.view.next !== undefined);
      } else {
        console.error('Failed to fetch entries');
      }
    } catch (error) {
      console.error('Error fetching entries:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadMoreEntries = () => {
    if (hasMore && !loading) {
      setPage(prevPage => {
        const nextPage = prevPage + 1;
        fetchEntries(nextPage);
        return nextPage;
      });
    }
  };

  const handleScroll = (event: NativeSyntheticEvent<NativeScrollEvent>) => {
    const { contentOffset } = event.nativeEvent;
    if (contentOffset.y <= 0 && hasMore && !loading) {
      loadMoreEntries();
    }
  };

  return (
    <View style={styles.container}>
      {loading && page === 1 ? (
        <ActivityIndicator size="large" color="#0000ff" />
      ) : (
        <FlatList
          ref={flatListRef}
          data={entries}
          renderItem={({ item }) => <Entry item={item} style={styles.entryContainer} />}
          keyExtractor={item => item['@id']}
          onScroll={handleScroll}
          ListFooterComponent={loading ? <ActivityIndicator size="small" color="#0000ff" /> : null}
          getItemLayout={(data, index) => (
            { length: 70, offset: 70 * index, index }
          )}
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  entryContainer: {
    padding: 10,
    borderBottomColor: '#ccc',
  },
});

export default EntriesScreen;