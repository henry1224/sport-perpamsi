export const statusLabel = (status) => ({
  draft: 'Draft', pending: 'Menunggu Verifikasi', verified: 'Terverifikasi', rejected: 'Ditolak', revision_required: 'Perlu Perbaikan', cancelled: 'Dibatalkan',
  registration_draft: 'Draft', registration_open: 'Pendaftaran Dibuka', registration_closed: 'Pendaftaran Ditutup', bracket_locked: 'Bracket Dikunci',
  scheduled: 'Terjadwal', ongoing: 'Sedang Berlangsung', live: 'Sedang Berlangsung', completed: 'Selesai', finished: 'Selesai', final: 'Final', disputed: 'Dalam Sengketa', archived: 'Diarsipkan',
}[status] || status);
