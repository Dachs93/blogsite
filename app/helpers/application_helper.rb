module ApplicationHelper
  MARKDOWN_OPTIONS = { render: { unsafe: true, hardbreaks: false } }.freeze

  # Markdown block (paragraphs, lists, code) to HTML. Only you can write content, so HTML in it is allowed.
  def markdown(text)
    Commonmarker.to_html(text.to_s, options: MARKDOWN_OPTIONS).html_safe
  end

  # Inline Markdown (links, bold, code) to HTML, without a wrapping <p>.
  def markdown_inline(text)
    markdown(text).to_str.strip.delete_prefix("<p>").delete_suffix("</p>").html_safe
  end

  def page_title
    if content_for?(:title)
      "#{content_for(:title)} · #{profile.name}"
    elsif profile.headline.present?
      "#{profile.name} · #{profile.headline}"
    else
      profile.name
    end
  end

  def page_description
    content_for(:description).presence || profile.search_description.presence || profile.headline
  end

  def current_page_attr(section)
    { aria: { current: ("page" if @section == section) } }
  end

  def long_date(date) = tag.time(date.strftime("%-d %B %Y"), datetime: date.iso8601)
  def short_date(date) = tag.time(date.strftime("%-d %b %Y"), datetime: date.iso8601)

  def status_badge(post)
    tag.span("#{post.status}: only you can see this", class: "badge") unless post.published?
  end

  # A labelled input or textarea, with an optional hint and its validation error.
  def field(form, name, label, type: :text, hint: nil, **attrs)
    id = "f-#{name}"
    error = form.object.errors[name].first
    described_by = []
    after = []

    if hint.present?
      described_by << "#{id}-hint"
      after << tag.p(hint, class: "hint", id: "#{id}-hint")
    end
    if error
      described_by << "#{id}-error"
      after << tag.p(error, class: "field-error", id: "#{id}-error")
      attrs[:"aria-invalid"] = "true"
    end
    attrs[:"aria-describedby"] = described_by.join(" ") if described_by.any?

    control = type == :textarea ? form.text_area(name, id: id, **attrs) : form.public_send("#{type}_field", name, id: id, **attrs)

    tag.div(class: [ "field", ("has-error" if error) ]) do
      form.label(name, label, for: id) + control + safe_join(after)
    end
  end

  def form_errors_notice(record)
    if record.errors.any?
      tag.p("Please fix the fields marked below. Nothing was saved yet.", class: "notice error", role: "alert")
    end
  end
end
