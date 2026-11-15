class Post < ApplicationRecord
  normalizes :title, :summary, with: ->(text) { text.strip }
  normalizes :body, with: ->(text) { text.gsub("\r\n", "\n") }

  before_validation :set_slug
  before_validation { self.published_on ||= Date.current }

  validates :title, presence: { message: "Give the post a title." }, length: { maximum: 255 }
  validates :slug, presence: { message: "Use at least one letter or number." },
    uniqueness: { message: "Another post already uses this address. Choose a different one." },
    length: { maximum: 190 }
  validates :summary, length: { maximum: 500 }
  validates :published_on, presence: { message: "Use a date like 2026-11-11." }

  # Visible to everyone: not a draft, and its date has arrived (so posts can be scheduled).
  scope :published, -> { where(draft: false).where(published_on: ..Date.current) }
  scope :newest_first, -> { order(published_on: :desc, id: :desc) }

  def published? = status == "Published"

  def status
    if draft? then "Draft"
    elsif published_on > Date.current then "Scheduled"
    else "Published"
    end
  end

  private
    # "Fáilte, World!" becomes "failte-world". A blank address is made from the title.
    def set_slug
      self.slug = (slug.presence || title.to_s).parameterize.first(190).delete_suffix("-")
    end
end
