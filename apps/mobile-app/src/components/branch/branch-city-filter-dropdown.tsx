import { Dropdown } from 'react-native-element-dropdown';

import { Text } from '@/components/ui/text';

export type BranchCityOption = {
  label: string;
  value: string;
};

type BranchCityFilterDropdownProps = {
  data: BranchCityOption[];
  value: string | null;
  onChange: (value: string) => void;
  active?: boolean;
};

export function BranchCityFilterDropdown({
  data,
  value,
  onChange,
  active = false,
}: BranchCityFilterDropdownProps) {
  return (
    <Dropdown
      style={{
        height: 40,
        borderWidth: 1,
        borderColor: active ? '#e8a020' : '#d6d3d1',
        borderRadius: 6,
        paddingHorizontal: 12,
        backgroundColor: active ? '#fffbeb' : '#ffffff',
      }}
      containerStyle={{
        borderRadius: 8,
        borderWidth: 1,
        borderColor: '#e7e5e4',
        marginTop: 4,
        overflow: 'hidden',
      }}
      itemContainerStyle={{
        borderBottomWidth: 1,
        borderBottomColor: '#f5f5f4',
      }}
      itemTextStyle={{
        fontSize: 14,
        color: '#44403c',
      }}
      activeColor="#fffbeb"
      selectedTextStyle={{
        fontSize: 14,
        color: '#44403c',
      }}
      placeholderStyle={{
        fontSize: 14,
        color: '#a8a29e',
      }}
      inputSearchStyle={{
        height: 40,
        fontSize: 14,
        borderRadius: 6,
        borderColor: '#d6d3d1',
        color: '#44403c',
      }}
      iconStyle={{
        width: 18,
        height: 18,
      }}
      data={data}
      labelField="label"
      valueField="value"
      placeholder="Filter kota"
      search
      searchPlaceholder="Cari kota..."
      value={value}
      onChange={(item) => onChange(item.value)}
      renderItem={(item, selected) => (
        <Text className={`px-3 py-3 text-sm ${selected ? 'text-[#b45309]' : 'text-stone-800'}`}>
          {item.label}
        </Text>
      )}
    />
  );
}
