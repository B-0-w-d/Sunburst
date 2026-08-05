/**
 * ==========================================================================
 * FILE: showManagement.js
 * Chức năng: Quản lý hiển thị danh sách show, tải danh sách bài hát cho modal
 * và xử lý tạo show mới sử dụng hàm dùng chung fetchWithAuth.
 * ==========================================================================
 */

import { fetchWithAuth } from './auth.js';

document.addEventListener("DOMContentLoaded", function () {
    showManagement.init();
});

const showManagement = {
    init: function () {
        this.loadShowsList();
        this.loadAllSongsForModal();

        const saveBtn = document.getElementById('saveNewShowBtn');
        if (saveBtn) {
            const self = this;
            saveBtn.addEventListener('click', function () {
                self.createNewShow();
            });
        }
    },

    loadShowsList: async function () {
        try {
            // Sử dụng fetchWithAuth gọn gàng, tự động đính kèm token và xử lý 401
            const result = await fetchWithAuth('/api/shows');
            if (!result) return;

            const shows = result.data || result;
            const container = document.getElementById('showsGridContainer');
            if (!container) return;

            container.innerHTML = '';

            if (!shows || shows.length === 0) {
                container.innerHTML = `<div class="col-12 text-center text-muted py-5">Chưa có show diễn nào được tạo.</div>`;
                return;
            }

            shows.forEach(show => {
                const showId = show.id || show._id;
                const title = show.title || 'Show không tên';
                const date = show.date || 'Chưa rõ thời gian';
                const location = show.location || 'Chưa rõ địa điểm';
                const setlistCount = show.setlist ? show.setlist.length : 0;

                container.innerHTML += `
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-dark fw-bold">${title}</h5>
                                <p class="card-text text-muted mb-1" style="font-size: 14px;">📅 ${date}</p>
                                <p class="card-text text-muted mb-2" style="font-size: 14px;">📍 ${location}</p>
                                <p class="card-text mb-3" style="font-size: 13px;"><span class="badge bg-info text-dark">🎵 ${setlistCount} bài hát trong setlist</span></p>

                                <div class="mt-auto">
                                    <a href="/shows/${showId}/manage" class="btn btn-outline-primary w-100 btn-sm">Quản Lý Setlist & Thành Viên</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } catch (err) {
            console.error("Lỗi tải danh sách show:", err);
        }
    },

    loadAllSongsForModal: async function () {
        try {
            const result = await fetchWithAuth('/api/songs');
            if (!result) return;

            const songs = result.data || result;
            const container = document.getElementById('songsCheckboxList');
            if (!container) return;

            container.innerHTML = '';

            if (!songs || songs.length === 0) {
                container.innerHTML = `<span class="text-muted">Kho bài hát trống. Hãy tạo bài hát trước.</span>`;
                return;
            }

            songs.forEach(song => {
                const songId = song.id || song._id;
                container.innerHTML += `
                    <div class="form-check">
                        <input class="form-check-input song-checkbox" type="checkbox" value="${songId}" id="song_${songId}">
                        <label class="form-check-label" for="song_${songId}">
                            <strong>${song.title}</strong> <span class="text-muted">(${song.default_description || ''})</span>
                        </label>
                    </div>
                `;
            });
        } catch (err) {
            console.error("Lỗi tải kho bài hát:", err);
        }
    },

    createNewShow: async function () {
        const title = document.getElementById('showTitleInput')?.value.trim();
        const date = document.getElementById('showDateInput')?.value;
        const location = document.getElementById('showLocationInput')?.value.trim();

        if (!title || !date || !location) {
            alert('Vui lòng điền đầy đủ thông tin Tên show, Thời gian và Địa điểm!');
            return;
        }

        const selectedSongIds = [];
        document.querySelectorAll('.song-checkbox:checked').forEach(cb => {
            selectedSongIds.push(cb.value);
        });

        try {
            // Gọi POST tạo show qua fetchWithAuth
            const showResult = await fetchWithAuth('/api/shows', {
                method: 'POST',
                body: JSON.stringify({ title, date, location })
            });

            if (!showResult) return;

            const createdShow = showResult.data || showResult;
            const newShowId = createdShow.id || createdShow._id;

            // Nếu có chọn bài hát, thêm vào setlist của show vừa tạo
            if (selectedSongIds.length > 0 && newShowId) {
                await fetchWithAuth(`/api/shows/${newShowId}/setlist`, {
                    method: 'POST',
                    body: JSON.stringify({ song_ids: selectedSongIds })
                });
            }

            alert('Tạo show thành công!');

            const modalEl = document.getElementById('createShowModal');
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            document.getElementById('createShowForm')?.reset();

            // Tải lại danh sách show
            this.loadShowsList();

        } catch (err) {
            console.error(err);
            alert(err.message || 'Có lỗi xảy ra khi tạo show.');
        }
    }
};
