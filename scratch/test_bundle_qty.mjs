import { countBundleItems, cartTotals, buildWaMessage } from '../assets/js/shared/order.js';

const mockOrderItems = [
  {
    name: 'Oreo Shake',
    price: 17000,
    qty: 1,
    isBundle: false,
    choices: ['Ice', 'Regular', 'Milk', 'Less Sugar', 'Normal Ice']
  },
  {
    name: 'Bundling 3 Cup Reguler (Rp35.000)',
    price: 35000,
    qty: 1,
    isBundle: true,
    slots: 3,
    selections: [
      { name: 'Kopi Kenangan Mantan', choices: ['Ice', 'Normal Ice', 'Normal Sugar'] },
      { name: 'Kopi Kenangan Mantan', choices: ['Ice', 'Normal Ice', 'Normal Sugar'] },
      { name: 'Kopi Kenangan Mantan', choices: ['Ice', 'Normal Ice', 'Normal Sugar'] }
    ],
    fixedItems: []
  },
  {
    name: 'Taro Cloud Matcha',
    price: 15000,
    qty: 1,
    isBundle: false,
    choices: ['Ice', 'Regular', 'Milk', 'Normal Sugar', 'Normal Ice']
  },
  {
    name: 'Kopi Kenangan Mantan',
    price: 13500,
    qty: 1,
    isBundle: false,
    choices: ['Ice', 'Regular', 'Kenangan Blend', 'Milk', 'Less Sugar', 'Normal Ice']
  }
];

const mockBundling4pcs = {
  name: 'Bundling 4 Pcs (Rp35.000)',
  price: 35000,
  qty: 1,
  isBundle: true,
  slots: 2,
  selections: [
    { name: 'Kopi Kenangan Mantan' },
    { name: 'Americano' }
  ],
  fixedItems: ['2 Roti Coklat Klasik']
};

console.log('Testing single item count:', countBundleItems(mockOrderItems[0])); // should be 1
console.log('Testing Bundling 3 count:', countBundleItems(mockOrderItems[1])); // should be 3
console.log('Testing Bundling 4pcs count:', countBundleItems(mockBundling4pcs)); // should be 4 (2 selections + 2 roti)

const totals = cartTotals(mockOrderItems, {});
console.log('Totals qty for screenshot order:', totals.qty); // should be 1 + 3 + 1 + 1 = 6

const waMsg = buildWaMessage({
  customerName: 'Budi',
  brandName: 'Kopi Kenangan',
  outlet: 'Fairway Nine',
  pickup: 'Sekarang',
  items: mockOrderItems,
  ...totals,
  code: 'ZF-TEST1'
});

console.log('\nGenerated WhatsApp message:\n');
console.log(waMsg);

if (totals.qty === 6 && countBundleItems(mockBundling4pcs) === 4) {
  console.log('\nSUCCESS! All assertions passed.');
} else {
  console.error('\nFAILURE! Assertions failed.');
  process.exit(1);
}

