import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../models/chat_models.dart';
import '../../services/chat_api_service.dart';
import '../../theme/app_colors.dart';
import '../../widgets/image_viewer_dialog.dart';

class ChatRoomScreen extends StatefulWidget {
  final int roomId;
  final String? initialTitle;

  const ChatRoomScreen({
    super.key,
    required this.roomId,
    this.initialTitle,
  });

  @override
  State<ChatRoomScreen> createState() => _ChatRoomScreenState();
}

class _ChatRoomScreenState extends State<ChatRoomScreen> {
  final TextEditingController _msgController = TextEditingController();
  final ScrollController _scrollController   = ScrollController();
  final ImagePicker _picker                  = ImagePicker();

  ChatRoomModel? _room;
  List<ChatParticipantModel> _participants = [];
  List<ChatMessageModel> _messages         = [];

  bool _loading     = true;
  bool _sending     = false;
  File? _selectedImage;
  Timer? _pollTimer;
  int _lastMessageId = 0;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
    _startPolling();
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _msgController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _startPolling() {
    _pollTimer?.cancel();
    _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      _pollNewMessages();
    });
  }

  Future<void> _loadInitialData() async {
    setState(() => _loading = true);
    final data = await ChatApiService.getRoomDetail(widget.roomId);
    if (!mounted) return;

    if (data != null) {
      setState(() {
        _room         = data['room'] as ChatRoomModel?;
        _participants = (data['participants'] as List<ChatParticipantModel>?) ?? [];
        _messages     = (data['messages'] as List<ChatMessageModel>?) ?? [];
        _loading      = false;
        if (_messages.isNotEmpty) {
          _lastMessageId = _messages.last.id;
        }
      });
      _scrollToBottom();
    } else {
      setState(() => _loading = false);
    }
  }

  Future<void> _pollNewMessages() async {
    if (!mounted || _loading) return;

    final result = await ChatApiService.pollMessages(widget.roomId, afterId: _lastMessageId);
    if (!mounted) return;

    final newMsgs = result['messages'] as List<ChatMessageModel>;
    final deletedIds = result['deleted_ids'] as List<int>;
    final readStatuses = result['read_statuses'] as Map<String, dynamic>;

    bool hasChanges = false;

    if (newMsgs.isNotEmpty) {
      _messages.addAll(newMsgs);
      _lastMessageId = _messages.last.id;
      hasChanges = true;
    }

    if (deletedIds.isNotEmpty) {
      for (int i = 0; i < _messages.length; i++) {
        if (deletedIds.contains(_messages[i].id)) {
          // Tandai sebagai ditarik
          hasChanges = true;
        }
      }
    }

    if (readStatuses.isNotEmpty || hasChanges) {
      setState(() {});
      if (newMsgs.isNotEmpty) {
        _scrollToBottom();
      }
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent + 80,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final picked = await _picker.pickImage(source: source, imageQuality: 75);
      if (picked != null) {
        setState(() => _selectedImage = File(picked.path));
      }
    } catch (_) {}
  }

  Future<void> _sendMessage() async {
    final text = _msgController.text.trim();
    if (text.isEmpty && _selectedImage == null) return;
    if (_sending) return;

    setState(() => _sending = true);
    final sent = await ChatApiService.sendMessage(
      widget.roomId,
      message: text.isNotEmpty ? text : null,
      attachment: _selectedImage,
    );

    if (!mounted) return;

    if (sent != null) {
      setState(() {
        _messages.add(sent);
        _lastMessageId = sent.id;
        _msgController.clear();
        _selectedImage = null;
        _sending = false;
      });
      _scrollToBottom();
    } else {
      setState(() => _sending = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal mengirim pesan. Coba lagi.'),
          backgroundColor: AppColors.red500,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _confirmDeleteMessage(ChatMessageModel msg) async {
    if (!msg.canDelete) return;

    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Tarik Pesan', style: TextStyle(fontWeight: FontWeight.bold)),
        content: const Text('Tarik pesan ini untuk semua anggota di ruang obrolan?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.red500),
            child: const Text('Tarik Pesan'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final ok = await ChatApiService.deleteMessage(msg.id);
      if (ok && mounted) {
        _pollNewMessages();
      }
    }
  }

  void _showParticipantsSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40, height: 4,
                    decoration: BoxDecoration(color: AppColors.gray200, borderRadius: BorderRadius.circular(2)),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(color: AppColors.blue50, borderRadius: BorderRadius.circular(12)),
                      child: const Icon(Icons.groups_rounded, color: AppColors.blue600, size: 24),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _room?.name ?? 'Konsultasi Segitiga',
                            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray900),
                          ),
                          Text(
                            'Kelas: ${_room?.className ?? "-"}',
                            style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Divider(height: 1, color: AppColors.gray200),
                const SizedBox(height: 12),
                const Text('Anggota Ruang Obrolan:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray700)),
                const SizedBox(height: 10),
                ..._participants.map((p) {
                  final (badgeColor, badgeBg, roleIcon) = switch (p.role) {
                    'Wali Kelas' => (const Color(0xFF6D28D9), const Color(0xFFEDE9FE), Icons.school_rounded),
                    'Orang Tua'  => (const Color(0xFFB45309), const Color(0xFFFEF3C7), Icons.family_restroom_rounded),
                    'Siswa'      => (const Color(0xFF047857), const Color(0xFFD1FAE5), Icons.face_rounded),
                    _            => (AppColors.blue600, AppColors.blue50, Icons.person_rounded),
                  };

                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.gray50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.gray100),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 38, height: 38,
                          decoration: BoxDecoration(color: badgeBg, shape: BoxShape.circle),
                          child: Icon(roleIcon, color: badgeColor, size: 20),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      p.name,
                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.gray800),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(color: badgeBg, borderRadius: BorderRadius.circular(10)),
                                    child: Text(
                                      p.role,
                                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: badgeColor),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(
                                p.statusText,
                                style: TextStyle(
                                  fontSize: 11,
                                  color: p.statusText.contains('sekarang') ? AppColors.green600 : AppColors.gray400,
                                  fontWeight: p.statusText.contains('sekarang') ? FontWeight.w600 : FontWeight.normal,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final title = _room?.name ?? widget.initialTitle ?? 'Konsultasi';

    // Subtitle ringkasan nama Wali Kelas, Siswa, Ortu
    String subtitle = '';
    if (_room != null) {
      final parts = <String>[];
      if (_room!.homeroomTeacherName.isNotEmpty && _room!.homeroomTeacherName != 'Belum ditentukan') {
        parts.add('Wali: ${_room!.homeroomTeacherName}');
      }
      if (_room!.studentName.isNotEmpty) {
        parts.add('Siswa: ${_room!.studentName}');
      }
      if (_room!.parentNames.isNotEmpty && _room!.parentNames != 'Belum terhubung') {
        parts.add('Ortu: ${_room!.parentNames}');
      }
      subtitle = parts.join(' • ');
    }

    return Scaffold(
      backgroundColor: const Color(0xFFEBE5DF), // WhatsApp / Telegram light background
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F2460),
        foregroundColor: Colors.white,
        elevation: 1,
        titleSpacing: 0,
        title: InkWell(
          onTap: _showParticipantsSheet,
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 4),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 19,
                  backgroundColor: Colors.white.withValues(alpha: 0.20),
                  child: const Icon(Icons.groups_rounded, color: Colors.white, size: 22),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Colors.white),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      if (subtitle.isNotEmpty)
                        Text(
                          subtitle,
                          style: const TextStyle(fontSize: 10, color: Color(0xFFCBD5E1)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        )
                      else
                        const Text(
                          'Ketuk untuk info anggota',
                          style: TextStyle(fontSize: 10, color: Color(0xFFCBD5E1)),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.info_outline_rounded, color: Colors.white),
            tooltip: 'Detail Anggota',
            onPressed: _showParticipantsSheet,
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // ─── Header Info Banner: Segitiga Konsultasi ───
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
              color: const Color(0xFFFEF3C7),
              child: Row(
                children: [
                  const Icon(Icons.security_rounded, size: 16, color: Color(0xFFB45309)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Ruang konsultasi resmi: Siswa, Orang Tua, & Wali Kelas (${_room?.className ?? "Kelas"}).',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF92400E), fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),

            // ─── Chat Messages List ───
            Expanded(
              child: _loading
                  ? const Center(child: CircularProgressIndicator())
                  : _messages.isEmpty
                      ? _buildEmptyState()
                      : ListView.builder(
                          controller: _scrollController,
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                          itemCount: _messages.length,
                          itemBuilder: (ctx, idx) {
                            final msg = _messages[idx];
                            final showDatePill = idx == 0 || _messages[idx - 1].date != msg.date;

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                if (showDatePill) _buildDatePill(msg.date),
                                _buildChatBubble(msg),
                              ],
                            );
                          },
                        ),
            ),

            // ─── Image attachment preview chip ───
            if (_selectedImage != null)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                color: Colors.white,
                child: Row(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.file(_selectedImage!, width: 44, height: 44, fit: BoxFit.cover),
                    ),
                    const SizedBox(width: 10),
                    const Expanded(
                      child: Text('Foto siap dikirim', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: AppColors.red500, size: 20),
                      onPressed: () => setState(() => _selectedImage = null),
                    ),
                  ],
                ),
              ),

            // ─── Bottom Input Bar (WhatsApp / Telegram Style) ───
            _buildInputBar(),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Container(
        margin: const EdgeInsets.all(24),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.90),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(14),
              decoration: const BoxDecoration(color: AppColors.blue50, shape: BoxShape.circle),
              child: const Icon(Icons.chat_bubble_outline_rounded, color: AppColors.blue600, size: 36),
            ),
            const SizedBox(height: 12),
            const Text(
              'Mulai Percakapan',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray800),
            ),
            const SizedBox(height: 6),
            const Text(
              'Belum ada pesan di ruang konsultasi ini. Sapa wali kelas atau orang tua untuk memulai bimbingan.',
              style: TextStyle(fontSize: 12, color: AppColors.gray500, height: 1.4),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDatePill(String dateStr) {
    return Center(
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 10),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: 0.20),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          dateStr,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
        ),
      ),
    );
  }

  Widget _buildChatBubble(ChatMessageModel msg) {
    final isMe = msg.isMe;

    final (badgeColor, badgeBg) = switch (msg.senderRole) {
      'Wali Kelas' => (const Color(0xFF6D28D9), const Color(0xFFEDE9FE)),
      'Orang Tua'  => (const Color(0xFFB45309), const Color(0xFFFEF3C7)),
      'Siswa'      => (const Color(0xFF047857), const Color(0xFFD1FAE5)),
      _            => (AppColors.blue700, AppColors.blue50),
    };

    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: GestureDetector(
        onLongPress: msg.canDelete ? () => _confirmDeleteMessage(msg) : null,
        child: Container(
          constraints: BoxConstraints(
            maxWidth: MediaQuery.of(context).size.width * 0.78,
          ),
          margin: const EdgeInsets.symmetric(vertical: 3),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: isMe ? const Color(0xFFDCF8C6) : Colors.white, // WhatsApp green for outgoing, clean white for incoming
            borderRadius: BorderRadius.only(
              topLeft: const Radius.circular(16),
              topRight: const Radius.circular(16),
              bottomLeft: isMe ? const Radius.circular(16) : const Radius.circular(4),
              bottomRight: isMe ? const Radius.circular(4) : const Radius.circular(16),
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 3,
                offset: const Offset(0, 1),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ─── Nama & Role Pengirim (PENTING: Selalu cantumkan nama & role agar jelas) ───
              if (!isMe) ...[
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Flexible(
                      child: Text(
                        msg.senderName,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: badgeColor,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                      decoration: BoxDecoration(color: badgeBg, borderRadius: BorderRadius.circular(6)),
                      child: Text(
                        msg.senderRole,
                        style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: badgeColor),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
              ],

              // ─── Foto Lampiran ───
              if (!msg.isDeleted && msg.attachmentUrl != null && msg.attachmentUrl!.isNotEmpty) ...[
                GestureDetector(
                  onTap: () => ImageViewerDialog.show(
                    context,
                    imageUrl: msg.attachmentUrl!,
                    title: msg.senderName,
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      msg.attachmentUrl!,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                    ),
                  ),
                ),
                const SizedBox(height: 6),
              ],

              // ─── Isi Pesan ───
              Text(
                msg.message,
                style: TextStyle(
                  fontSize: 13.5,
                  height: 1.35,
                  color: msg.isDeleted ? AppColors.gray400 : AppColors.gray900,
                  fontStyle: msg.isDeleted ? FontStyle.italic : FontStyle.normal,
                ),
              ),
              const SizedBox(height: 3),

              // ─── Baris Timestamp & Status Centang (WA / Telegram Style) ───
              Row(
                mainAxisSize: MainAxisSize.min,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  const Spacer(),
                  Text(
                    msg.time,
                    style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                  ),
                  if (isMe && !msg.isDeleted) ...[
                    const SizedBox(width: 4),
                    Icon(
                      Icons.done_all_rounded,
                      size: 15,
                      color: msg.readStatus == 'read_all' ? const Color(0xFF38BDF8) : const Color(0xFF94A3B8), // Biru jika dibaca semua, abu jika terkirim
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInputBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      decoration: const BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(color: Color(0x0F000000), blurRadius: 4, offset: Offset(0, -1)),
        ],
      ),
      child: Row(
        children: [
          // Tombol Lampirkan Foto (Kamera / Galeri)
          IconButton(
            icon: const Icon(Icons.attach_file_rounded, color: AppColors.gray600),
            tooltip: 'Lampirkan Foto',
            onPressed: () {
              showModalBottomSheet(
                context: context,
                shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
                builder: (_) => SafeArea(
                  child: Wrap(
                    children: [
                      ListTile(
                        leading: const Icon(Icons.camera_alt_rounded, color: AppColors.blue600),
                        title: const Text('Ambil Foto Kamera'),
                        onTap: () {
                          Navigator.pop(context);
                          _pickImage(ImageSource.camera);
                        },
                      ),
                      ListTile(
                        leading: const Icon(Icons.photo_library_rounded, color: AppColors.emerald600),
                        title: const Text('Pilih dari Galeri'),
                        onTap: () {
                          Navigator.pop(context);
                          _pickImage(ImageSource.gallery);
                        },
                      ),
                    ],
                  ),
                ),
              );
            },
          ),

          // Kolom Input Pesan Teks
          Expanded(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(24),
              ),
              child: TextField(
                controller: _msgController,
                textCapitalization: TextCapitalization.sentences,
                minLines: 1,
                maxLines: 4,
                decoration: const InputDecoration(
                  hintText: 'Tulis pesan...',
                  hintStyle: TextStyle(fontSize: 13, color: AppColors.gray400),
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  isDense: true,
                  contentPadding: EdgeInsets.symmetric(vertical: 10),
                ),
              ),
            ),
          ),
          const SizedBox(width: 6),

          // Tombol Kirim Pesan
          Container(
            decoration: const BoxDecoration(
              color: Color(0xFF0F2460),
              shape: BoxShape.circle,
            ),
            child: IconButton(
              icon: _sending
                  ? const SizedBox(
                      width: 18, height: 18,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                    )
                  : const Icon(Icons.send_rounded, color: Colors.white, size: 20),
              onPressed: _sending ? null : _sendMessage,
            ),
          ),
        ],
      ),
    );
  }
}
