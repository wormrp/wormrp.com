/*
 * Copyright (c) 2023 Keira Dueck <sylae@calref.net>
 * Use of this source code is governed by the MIT license, which
 * can be found in the LICENSE file.
 */

document.addEventListener('DOMContentLoaded', (event) => {

    document.querySelectorAll(".reply").forEach(function (post, key, parent) {
        let replyForm = post.querySelector(".replyForm");
        let editForm = post.querySelector(".editForm");
        let replyButton = post.querySelector(".is-reply-button");
        let editButton = post.querySelector(".is-edit-button");

        replyButton.addEventListener("click", function (event) {
            if (replyForm.classList.contains("is-hidden")) {
                replyForm.classList.remove("is-hidden");
                editForm.classList.add("is-hidden");
            } else {
                replyForm.classList.add("is-hidden");
            }
        });
        editButton.addEventListener("click", function (event) {
            if (editForm.classList.contains("is-hidden")) {
                editForm.classList.remove("is-hidden");
                replyForm.classList.add("is-hidden");
            } else {
                editForm.classList.add("is-hidden");
            }
        });
        replyForm.classList.add("is-hidden");
        editForm.classList.add("is-hidden");
    });
});