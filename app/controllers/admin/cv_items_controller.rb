module Admin
  class CvItemsController < BaseController
    before_action :set_item, only: %i[edit update destroy move]

    def new
      section = params[:section].to_s
      return head :not_found unless CvItem::SECTIONS.key?(section)

      # A one-box section that already has its box: edit that instead of adding another.
      existing = CvItem.where(section: section).ordered.first
      if CvItem::SECTIONS[section][:single] && existing
        redirect_to edit_admin_cv_item_path(existing)
      else
        @item = CvItem.new(section: section)
      end
    end

    def create
      @item = CvItem.new(item_params.merge(section: params.dig(:cv_item, :section)))
      if @item.save
        redirect_to admin_cv_path(anchor: @item.section), notice: "Saved."
      else
        render :new, status: :unprocessable_content
      end
    end

    def edit
    end

    def update
      if @item.update(item_params)
        redirect_to admin_cv_path(anchor: @item.section), notice: "Saved."
      else
        render :edit, status: :unprocessable_content
      end
    end

    def destroy
      @item.destroy
      redirect_to admin_cv_path(anchor: @item.section), notice: "Deleted.", status: :see_other
    end

    def move
      @item.move(params[:direction])
      redirect_to admin_cv_path(anchor: @item.section), status: :see_other
    end

    private
      def set_item
        @item = CvItem.find(params[:id])
      end

      def item_params
        params.expect(cv_item: CvItem::FIELDS)
      end
  end
end
