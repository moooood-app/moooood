import { Text } from "@react-navigation/elements";
import { View, ViewStyle } from "react-native";
import { format } from 'timeago.js';
import { getTimeZone } from "react-native-localize";
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(utc);
dayjs.extend(timezone);

export interface EntryInterface {
    '@id': string;
    content: string;
    createdAt: string;
}

interface EntryProps {
    item: EntryInterface;
    style: ViewStyle;
}


const Entry = ({ item, style }: EntryProps) => {
    dayjs.tz.setDefault(getTimeZone())

    const localizedDate = dayjs.utc(item.createdAt);

    return (
        <View style={style}>
            <Text>{item.content}</Text>
            <Text>Original Date (UTC String): {item.createdAt}</Text>
            <Text>Converted to Local: {localizedDate.format()}</Text>
            <Text>Using Date::toLocaleTimeString: {localizedDate.toDate().toLocaleTimeString()}</Text>
            <Text>{format(localizedDate.toDate())}</Text>
        </View>
    )
};

export default Entry;