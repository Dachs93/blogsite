module Admin
  class ProfilesController < BaseController
    def update
      if profile.update(profile_params)
        redirect_to admin_cv_path, notice: "Saved."
      else
        @items = CvItem.by_section
        render "admin/cv/show", status: :unprocessable_content
      end
    end

    private
      def profile_params
        params.expect(profile: %i[name headline location email links summary search_description machine_note])
      end
  end
end
