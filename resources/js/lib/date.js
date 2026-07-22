const dateParts = (value) => String(value).slice(0, 10).split('-');

export const formatDate = (value) => {
  if (!value) return '—';
  const [year, month, day] = dateParts(value);
  return `${day}-${month}-${year.slice(-2)}`;
};

export const formatDateTime = (value) => value
  ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit', hour12: false }).format(new Date(value)).replace('.', '')
  : '—';
