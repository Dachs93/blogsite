# This file is auto-generated from the current state of the database. Instead
# of editing this file, please use the migrations feature of Active Record to
# incrementally modify your database, and then regenerate this schema definition.
#
# This file is the source Rails uses to define your schema when running `bin/rails
# db:schema:load`. When creating a new database, `bin/rails db:schema:load` tends to
# be faster and is potentially less error prone than running all of your
# migrations from scratch. Old migrations may fail to apply correctly if those
# migrations use external dependencies or application code.
#
# It's strongly recommended that you check this file into your version control system.

ActiveRecord::Schema[8.1].define(version: 2026_11_13_191142) do
  create_table "cv_items", force: :cascade do |t|
    t.string "section", null: false
    t.string "title", default: "", null: false
    t.string "subtitle", default: "", null: false
    t.string "dates", default: "", null: false
    t.string "meta", default: "", null: false
    t.string "url", default: "", null: false
    t.text "body", default: "", null: false
    t.integer "position", default: 0, null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["section", "position"], name: "index_cv_items_on_section_and_position"
  end

  create_table "posts", force: :cascade do |t|
    t.string "slug", null: false
    t.string "title", null: false
    t.string "summary", default: "", null: false
    t.text "body", default: "", null: false
    t.date "published_on", null: false
    t.boolean "draft", default: true, null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["draft", "published_on"], name: "index_posts_on_draft_and_published_on"
    t.index ["slug"], name: "index_posts_on_slug", unique: true
  end

  create_table "profiles", force: :cascade do |t|
    t.string "name", null: false
    t.string "headline", default: "", null: false
    t.string "location", default: "", null: false
    t.string "email", default: "", null: false
    t.text "links", default: "", null: false
    t.text "summary", default: "", null: false
    t.string "search_description", default: "", null: false
    t.text "machine_note", default: "", null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
  end

  create_table "sessions", force: :cascade do |t|
    t.integer "user_id", null: false
    t.string "ip_address"
    t.string "user_agent"
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["user_id"], name: "index_sessions_on_user_id"
  end

  create_table "users", force: :cascade do |t|
    t.string "email_address", null: false
    t.string "password_digest", null: false
    t.datetime "created_at", null: false
    t.datetime "updated_at", null: false
    t.index ["email_address"], name: "index_users_on_email_address", unique: true
  end

  add_foreign_key "sessions", "users"
end
