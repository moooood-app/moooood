import bentoml
from pydantic import Field, BaseModel
import typing as t

image = bentoml.images.Image(
        python_version='3.11',
        distro='alpine',
    ).requirements_file("requirements.txt")

class AnalyzeInput(BaseModel):
    entry: str = Field(
        title="Entry Text",
        description="The entry text to analyze for complexity.",
        example="The quick brown fox jumps over the lazy dog.",
        min_length=1,
    )

class AnalyzeOutput(BaseModel):
    flesch_kincaid_grade_level: float = Field(
        title="Flesch-Kincaid Grade Level",
        description=(
            "The measure of the readability of a text based on the average number of syllables per word and words per sentence. "
            "Higher values indicate more complex language. "
            "Minimum Value: 0 (easiest). "
            "Maximum Value: 100 (most difficult)."
        )
    )
    flesch_reading_ease: float = Field(
        title="Flesch Reading Ease",
        description=(
            "A score that indicates how easy or difficult a text is to read. "
            "Higher scores indicate easier readability, while lower scores indicate more complex text. "
            "Minimum Value: 0 (most difficult). "
            "Maximum Value: 100 (easiest)."
        )
    )
    gunning_fog_index: float = Field(
        title="Gunning Fog Index",
        description=(
            "The Gunning Fog Index estimates the years of formal education required to understand a piece of text. "
            "It considers the average number of words per sentence and the percentage of complex words "
            "(words with three or more syllables). "
            "Minimum Value: 0 (easiest). "
            "Maximum Value: No upper limit, but typically ranges from 6 to 20+."
        )
    )
    smog_index: float = Field(
        title="SMOG Index",
        description=(
            "The SMOG Index (Simple Measure of Gobbledygook) estimates the years of education needed to understand a text. "
            "It calculates the index based on the number of complex words (three or more syllables) in a sample of text. "
            "Minimum Value: 0 (easiest). "
            "Maximum Value: No upper limit, but typically ranges from 6 to 20+."
        )
    )
    automated_readability_index: float = Field(
        title="Automated Readability Index",
        description=(
            "A readability index estimating the years of education needed to understand the text, "
            "based on characters per word and sentences per 100 words. "
            "Minimum Value: 0 (easiest). "
            "Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)."
        )
    )
    coleman_liau_index: float = Field(
        title="Coleman-Liau Index",
        description=(
            "A readability index that calculates the grade level required to understand the text based on characters per word "
            "and sentences per 100 words. "
            "Minimum Value: -3 (rarely used in practice). "
            "Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)."
        )
    )
    linsear_write_formula: float = Field(
        title="Linsear Write Formula",
        description=(
            "A readability formula estimating the readability of a text based on the number of simple and complex words in a sample. "
            "Simple words have one or two syllables; complex words have three or more syllables. "
            "Minimum Value: 0 (easiest). "
            "Maximum Value: Typically ranges from 0 to 20+."
        )
    )
    dale_chall_readability_score: float = Field(
        title="Dale-Chall Readability Score",
        description=(
            "A readability formula estimating the text's difficulty by considering a list of 'easy' words. "
            "It calculates the percentage of words not on the list of easy words. "
            "Minimum Value: 0 (easiest). "
            "Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)."
        )
    )
    readability_consensus: float = Field(
        title="Readability Consensus",
        description=(
            "An average score of multiple readability formulas, including Flesch Reading Ease, Flesch-Kincaid Grade Level, Coleman-Liau Index, "
            "Automated Readability Index, and Dale-Chall Readability Score. "
            "Provides an overall assessment of text readability considering multiple factors. "
            "Minimum Value: 0 (most difficult). "
            "Maximum Value: Typically ranges from 0 to 100, with higher values indicating easier readability."
        )
    )

    class Config:
        title = "Text Complexity Metrics"
        json_schema_extra = {
            "example": {
                "flesch_kincaid_grade_level": 8.5,
                "flesch_reading_ease": 60.2,
                "gunning_fog_index": 11.3,
                "smog_index": 9.8,
                "automated_readability_index": 7.4,
                "coleman_liau_index": 10.1,
                "linsear_write_formula": 8.0,
                "dale_chall_readability_score": 6.7,
                "readability_consensus": 9.2,
            }
        }

@bentoml.service(
    image=image,
    name="complexity",
    title="Text Complexity Service",
    description="Analyze text complexity with multiple readability metrics.",
)
class Complexity:
    @bentoml.api(input_spec=AnalyzeInput, output_spec=AnalyzeOutput)
    def analyze(self, **params: t.Any) -> AnalyzeOutput:
        from textstat import textstat

        entry = params["entry"]

        return AnalyzeOutput(**{
            "flesch_kincaid_grade_level": textstat.flesch_kincaid_grade(entry),
            "flesch_reading_ease": textstat.flesch_reading_ease(entry),
            "gunning_fog_index": textstat.gunning_fog(entry),
            "smog_index": textstat.smog_index(entry),
            "automated_readability_index": textstat.automated_readability_index(entry),
            "coleman_liau_index": textstat.coleman_liau_index(entry),
            "linsear_write_formula": textstat.linsear_write_formula(entry),
            "dale_chall_readability_score": textstat.dale_chall_readability_score(entry),
            "readability_consensus": textstat.text_standard(entry, float_output=True),
        })
